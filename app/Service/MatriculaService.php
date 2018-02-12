<?php
/**
 * Created by PhpStorm.
 * User: evertonmuniz
 * Date: 07/02/18
 * Time: 22:44
 */

namespace App\Service;

use App\Models\Aluno;
use App\Models\Curso;
use App\Models\Matricula;
use App\Models\Pagamento;
use App\Models\TipoPagamento;
use Illuminate\Validation\ValidationException;

class MatriculaService
{
    /**
     * Matricula um aluno em um curso
     *
     * @param int $idAluno
     * @param int $idCurso
     * @param int $ano
     * @return Matricula
     * @throws \Exception
     */
    public function store(int $idAluno, int $idCurso, int $ano = null): Matricula
    {
        if (empty($ano)) {
            $ano = date('Y');
        }

        $matricula = new Matricula();

        if (!(Aluno::find($idAluno)) instanceof Aluno) {
            throw new \Exception('Aluno não encontrado');
        }

        if (!(Curso::find($idCurso)) instanceof Curso) {
            throw new \Exception('Curso não encontrado');
        }

        $this->validatePeriodoEAno($idAluno, $idCurso, $ano);

        try {
            $matricula->aluno_id = $idAluno;
            $matricula->curso_id = $idCurso;
            $matricula->ano = $ano;
            $matricula->save();
            $this->createPagamentos($matricula);
        } catch (\Exception $e) {
            throw new \Exception('Ocorreu um erro ao realizar a matricula' . $e->getMessage());
        }

        return $matricula;
    }

    public function list($filters)
    {
        $result = Matricula::orderByDesc('ano')->get();

        $matriculas = [];
        foreach ($result as $matricula) {
            if ($this->filter($filters, $matricula)) {
                $matriculas[] = $matricula;
            }
        }

        return $matriculas;
    }

    private function validatePeriodoEAno($idAluno, $idCurso, $ano)
    {
        $curso = Curso::find($idCurso);

        $sql = 'SELECT * FROM alunos
                JOIN matriculas ON alunos.id = matriculas.aluno_id
                JOIN cursos ON matriculas.curso_id = cursos.id
                WHERE alunos.id = :aluno
                  AND matriculas.ano = :ano
                  AND cursos.periodo_id = :periodo';

        $result = \DB::select($sql, ['aluno' => $idAluno, 'ano' => $ano, 'periodo' => $curso->periodo_id]);

        if (!empty($result)) {
            $error = ValidationException::withMessages([
                'O aluno já está matriculado em outro curso no mesmo periodo'
            ]);

            throw $error;
        }
    }

    /**
     * @param Matricula $matricula
     */
    private function createPagamentos($matricula)
    {
        Pagamento::create([
            'data' => (new \DateTime()),
            'valor' => $matricula->curso->valor_matricula,
            'matricula_id' => $matricula->id,
            'tipo_pagamento_id' => TipoPagamento::MATRICULA,
        ]);

        $duracao = $matricula->curso->duracao;

        for ($cont = 0; $cont < $duracao; $cont++) {
            $inicioCurso = (new \DateTime('first day of January ' . $matricula->ano));
            $data = $inicioCurso->modify(sprintf('+%s month', $cont));
            Pagamento::create([
                'data' => $data,
                'valor' => $matricula->curso->valor_mensalidade,
                'matricula_id' => $matricula->id,
                'tipo_pagamento_id' => TipoPagamento::MENSALIDADE,
            ]);
        }
    }

    /**
     * @param array $filters
     * @param Matricula $matricula
     * @return bool
     */
    private function filter($filters, $matricula)
    {
        if (!isset($filters['status'])) {
            $filters['status'] = 'ativos';
        }

        if ($filters['status'] == 'ativos') {
            if (!$matricula->isAtiva()) {
                return false;
            }
        }

        if ($filters['status'] == 'inativos') {
            if ($matricula->isAtiva()) {
                return false;
            }
        }

        if (isset($filters['pagamento']) && $filters['pagamento'] == 'inadimplente') {
            if (!$matricula->isPagamentoPendente()) {
                return false;
            }
        }

        if (isset($filters['pagamento']) && $filters['pagamento'] == 'adimplente') {
            if ($matricula->isPagamentoPendente()) {
                return false;
            }
        }

        return true;
    }

    public function calculaTroco($valorCobrado, $valorPago)
    {
        $textoArray = [];
        $troco = CalculoTroco::calcula($valorCobrado, $valorPago);
        foreach ($troco['notas'] as $nota => $quantidade) {
            $textoArray[] = sprintf( '%s nota(s) de %s', $quantidade, $nota);
        }

        foreach ($troco['moedas'] as $moeda => $quantidade) {
            $textoArray[] = sprintf( '%s moeda(s) de %s', $quantidade, $moeda);
        }

        return implode(PHP_EOL, $textoArray);
    }

    public function storePagamento($data)
    {
        $pagamento = Pagamento::find($data['pagamento']);
        $valorCobrado = $pagamento->valor;
        $valorPago = str_replace(',', '.', str_replace('.','', $data['valor_entregue']));

        if ($valorCobrado > $valorPago) {
            $error = ValidationException::withMessages([
                'O valor precisa ser igual ou maior que o valor cobrado'
            ]);

            throw $error;
        }

        $pagamento->data_pagamento = new \DateTime();
        $pagamento->valor_pago = $valorCobrado;
        $pagamento->save();

        return $pagamento->matricula;
    }
}