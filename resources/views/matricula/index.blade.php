@extends('template.default')

<?php
/** @var $matriculas \App\Models\Matricula[] */
?>

@section('specific-styles')
@endsection

@section('content')
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <h1 class="page-header">Matrículas</h1>
        <a href="{{route('matriculas.create')}}" class="btn btn-primary">Nova</a><br><br>

        <div class="row">
            <form method="GET" id="form-filter">
                <div id="toolbar">
                    <div class="col-sm-2 pull-left select-filter">
                        <select name="status" class="form-control">
                            <option value="todos" @if(\Request::get('status') == 'todos') selected @endif>Todos os
                                status
                            </option>
                            <option value="ativos"
                                    @if(\Request::get('status') == 'ativos' || empty(\Request::get('status'))) selected @endif>
                                Matrículas ativas
                            </option>
                            <option value="inativos" @if(\Request::get('status') == 'inativos') selected @endif>
                                Matrículas inativas
                            </option>
                        </select>
                    </div>
                </div>
                <div id="toolbar">
                    <div class="col-sm-2 pull-left">
                        <select name="pagamento" class="form-control select-filter">
                            <option value="todos" @if(\Request::get('pagamento') == 'todos') selected @endif>Todos os
                                status de pagamento
                            </option>
                            <option value="inadimplente"
                                    @if(\Request::get('pagamento') == 'inadimplente') selected @endif>Inadimplente
                            </option>
                            <option value="adimplente" @if(\Request::get('pagamento') == 'adimplente') selected @endif>
                                Adimplente
                            </option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <br>

        @if (empty($retorno['lines']))
            <div class="alert alert-warning">Nenhuma matrícula encontrada</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                    <tr>
                        <th>Ano</th>
                        <th>Curso</th>
                        <th>Aluno</th>
                        <th width="15%">Pagamento pendente</th>
                        <th width="15%">Ativa</th>
                        <th width="10%">Visualizar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($retorno['lines'] as $matricula)
                        <tr>

                            <td>{{$matricula->ano}}</td>
                            <td>{{$matricula->curso_nome}}</td>
                            <td>{{$matricula->aluno_nome}}</td>
                            <td>
                                @if(!$matricula->adimplente)
                                    <img src="/icons/icon-pending.png" title="Pagamento pendente" data-toggle="tooltip"
                                         class="table-icon">
                                @else
                                    <img src="/icons/icon-active.png" title="Nenhum pagamento pendente"
                                         data-toggle="tooltip"
                                         class="table-icon">

                                @endif
                            </td>
                            <td>
                                @if($matricula->ativa)
                                    <img src="/icons/icon-active.png" title="Ativa" data-toggle="tooltip"
                                         class="table-icon">
                                @else
                                    <img src="/icons/icon-inactive.png" title="Inativa" data-toggle="tooltip"
                                         class="table-icon">
                                @endif
                            </td>
                            <td>
                                <a href="{{route('matriculas.show',['matricula'=>$matricula->id])}}">
                                <span title="Visualizar" class="glyphicon glyphicon-open"
                                      aria-hidden="true" data-toggle="tooltip"></span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    @for($i=1;$i<$retorno['pages'];$i++)
                        @if($i == 1)
                            <li @if($retorno['currentPage'] == $i) class="active" @endif>
                                <a href="/?page={{$i}}&status={{\Request::get('status')}}&pagamento={{\Request::get('pagamento')}}">
                                    {{$i}}
                                </a>
                            </li>
                        @elseif($i == $retorno['currentPage'])
                            <li @if($retorno['currentPage'] == $i) class="active" @endif>
                                <a href="/?page={{$i}}&status={{\Request::get('status')}}&pagamento={{\Request::get('pagamento')}}">
                                    {{$i}}
                                </a>
                            </li>
                        @elseif($i <= $retorno['currentPage'] + 2 && $i >= $retorno['currentPage'] - 2)
                            <li @if($retorno['currentPage'] == $i) class="active" @endif>
                                <a href="/?page={{$i}}&status={{\Request::get('status')}}&pagamento={{\Request::get('pagamento')}}">
                                    {{$i}}
                                </a>
                            </li>
                        @elseif($i == $retorno['pages'] - 1)
                            <li @if($retorno['currentPage'] == $i) class="active" @endif>
                                <a href="/?page={{$i}}&status={{\Request::get('status')}}&pagamento={{\Request::get('pagamento')}}">
                                    {{$i}}
                                </a>
                            </li>
                        @endif

                    @endfor
                </ul>
            </nav>
        @endif
    </div>
@endsection

@section('specific-scripts')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })

        $('.select-filter').on('change', function () {
            $('#form-filter').submit();
        });
    </script>
@endsection