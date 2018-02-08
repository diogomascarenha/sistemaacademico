<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Service\AlunoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Faker\Factory as Faker;

class AlunoServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     * @throws \Exception
     */
    public function testStoreAluno()
    {
        $service = $this->getService();

        $data = [
            'nome' => 'Fulano',
            'cpf' => '44121672836',
            'rg' => '1234234',
            'data_nascimento' => '01/01/1990',
            'telefone' => '21 2362-2756'
        ];

        $service->store($data);

        $this->assertCount(1, Aluno::all()->all());
    }

    public function testShowAluno()
    {
        $faker = Faker::create();
        Aluno::create([
            'id' => 1,
            'nome' => $faker->name,
            'cpf' => $faker->text(11),
        ]);
        $service = $this->getService();

        $aluno = $service->show(1);

        $this->assertNotEmpty($aluno);
        $this->assertInstanceOf(Aluno::class, $aluno);
        $this->assertNotEmpty($aluno->nome);
        $this->assertNotEmpty($aluno->cpf);
    }

    public function testListAluno()
    {
        $this->createFakeDataAluno(5);
        $alunos = $this->getService()->list();

        $this->assertCount(5, $alunos);
    }

    public function testUpdateAluno()
    {
        $faker = Faker::create();
        Aluno::create([
            'id' => 1,
            'nome' => $faker->name,
            'cpf' => $faker->text(11),
        ]);
        $service = $this->getService();

        $data = [
            'nome' => 'Fulano',
            'cpf' => '44121672836',
            'rg' => '1234234',
            'data_nascimento' => '01/01/1990',
            'telefone' => '21 2362-2756'
        ];

        $service->update(1, $data);
        $aluno = Aluno::find(1);

        $this->assertEquals('Fulano', $aluno->nome);
        $this->assertEquals('44121672836', $aluno->cpf);
        $this->assertEquals('1234234', $aluno->rg);
        $this->assertEquals('1990-01-01', $aluno->data_nascimento);
        $this->assertEquals('21 2362-2756', $aluno->telefone);
    }

    public function testDeleteAluno()
    {
        $faker = Faker::create();
        Aluno::create([
            'id' => 1,
            'nome' => $faker->name,
            'cpf' => $faker->text(11),
        ]);

        Aluno::create([
            'id' => 2,
            'nome' => $faker->name,
            'cpf' => $faker->text(11),
        ]);

        $this->getService()->delete(2);

        $this->assertCount(1, Aluno::all());
    }

    /**
     * @return AlunoService
     */
    private function getService()
    {
        return new AlunoService();
    }

    private function createFakeDataAluno($quantity = 1)
    {
        factory(Aluno::class, $quantity)->create()->each(function ($model) {
            $model->save(factory(Aluno::class)->make()->getAttributes());
        });
    }
}
