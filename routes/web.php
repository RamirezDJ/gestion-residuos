<?php

use App\Http\Controllers\GraficasController;
use App\Http\Controllers\GraficasSemanalController;
use App\Http\Controllers\GraficasSubproductosController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MetaAnualController;
use App\Http\Controllers\ReporteDiarioController;
use App\Http\Controllers\PrediccionesZonasController;
use App\Http\Controllers\RegistroSemanalController;
use App\Http\Controllers\RegistroSubproductoController;
use App\Http\Controllers\IndicesCalidadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::get('/gensemanal/check-week', [RegistroSemanalController::class, 'checkWeek'])->name('gensemanal.checkWeek')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensemanal/search', [RegistroSemanalController::class, 'search'])->name('gensemanal.search')->middleware(['can:Acceso a Inicio']);
    Route::resource('/gensemanal', RegistroSemanalController::class)->middleware(['can:Acceso a Inicio']);
    Route::delete('gensemanal/delete-week/{fecha}', [RegistroSemanalController::class, 'destroyWeek'])->name('gensemanal.destroyWeek')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensemanal/edit/{fecha}/{turno}', [RegistroSemanalController::class, 'edit'])->name('gensemanal.editAll')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensemanal/edit-week/{fecha}', [RegistroSemanalController::class, 'editWeek'])->name('gensemanal.editWeek')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensemanal/show/{fecha}', [RegistroSemanalController::class, 'show'])->name('gensemanal.showAll')->middleware(['can:Acceso a Inicio']);
    Route::put('/gensemanal', [RegistroSemanalController::class, 'updateAll'])->name('gensemanal.updateAll')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensemanal/pdf/{fecha}', [RegistroSemanalController::class, 'GenerarPDF'])->name('gensemanal.pdf')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensemanal/excel/{fecha}', [RegistroSemanalController::class, 'GenerarExcel'])->name('gensemanal.excel')->middleware(['can:Acceso a Inicio']);


    Route::get('/graficassemanal', [GraficasSemanalController::class, 'index'])->name('graficassemanal.index')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassemanal/data', [GraficasSemanalController::class, 'fetchGraphData'])->name('graficassemanal.data')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassemanal/data/top3', [GraficasSemanalController::class, 'getTop3Generado'])->name('graficassemanal.data.top3')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassemanal/data/piechart', [GraficasSemanalController::class, 'getPorcentajeResiduos'])->name('graficassemanal.data.piechart')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassemanal/data/barchart', [GraficasSemanalController::class, 'getGraficoTotalResiduos'])->name('graficassemanal.data.barchart')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassemanal/data/linechart', [GraficasSemanalController::class, 'getGraficoTendenciaResiduos'])->name('graficassemanal.data.linechart')->middleware(['can:Acceso a Graficas']);

    Route::get('/gensubproductos/search', [RegistroSubproductoController::class, 'search'])->name('gensubproductos.search')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensubproductos/check-week', [RegistroSubproductoController::class, 'checkWeek'])->name('gensubproductos.checkWeek')->middleware(['auth:sanctum', 'verified']);
    Route::resource('/gensubproductos', RegistroSubproductoController::class)->middleware(['can:Acceso a Inicio']);
    Route::delete('/gensubproductos/destroy-week/{fecha}', [RegistroSubproductoController::class, 'destroyWeekSubproductos'])->name('gensubproductos.destroyWeek')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensubproductos/edit/{instituto_id}/{inicio}/{final}', [RegistroSubproductoController::class, 'edit'])->name('gensubproductos.editAll')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensubproductos/show/{instituto_id}/{inicio}/{final}', [RegistroSubproductoController::class, 'show'])->name('gensubproductos.showAll')->middleware(['can:Acceso a Inicio']);
    Route::put('/gensubproductos', [RegistroSubproductoController::class, 'updateMultiple'])->name('gensubproductos.updateMultiple');
    Route::get('/gensubproductos/pdf/{instituto_id}/{inicio}/{final}', [RegistroSubproductoController::class, 'GenerarPDF'])->name('gensubproductos.pdf')->middleware(['can:Acceso a Inicio']);
    Route::get('/gensubproductos/excel/{instituto_id}/{inicio}/{final}', [RegistroSubproductoController::class, 'GenerarExcel'])->name('gensubproductos.excel')->middleware(['can:Acceso a Inicio']);


    Route::get('/graficassubproductos', [GraficasSubproductosController::class, 'index'])->name('graficassubproductos.index')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassubproductos/data', [GraficasSubproductosController::class, 'fetchGraphData'])->name('graficassubproductos.data')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassubproductos/data/top3', [GraficasSubproductosController::class, 'getTop3Subproductos'])->name('graficassubproductos.data.top3')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassubproductos/data/piechart', [GraficasSubproductosController::class, 'getPorcentajeGenerado'])->name('graficassubproductos.data.piechart')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassubproductos/data/barchart', [GraficasSubproductosController::class, 'getGraficoBarras'])->name('graficassubproductos.data.barchart')->middleware(['can:Acceso a Graficas']);
    Route::get('/graficassubproductos/data/linechart', [GraficasSubproductosController::class, 'getGraficoTendencias'])->name('graficassubproductos.data.linechart')->middleware(['can:Acceso a Graficas']);

    Route::get('/evidenciasGenerado/search', [ImageController::class, 'search'])->name('evidenciasGenerado.search')
        ->middleware(['can:Acceso a Evidencias de Generación']);
    Route::resource('/evidenciasGenerado',  ImageController::class)
        ->middleware(['can:Acceso a Evidencias de Generación']);

    Route::get('/metaAnual', [MetaAnualController::class, 'index'])->name('metaAnual.index')->middleware(['can:Acceso a Meta Anual']);
    Route::get('/metaAnual/percapita', [MetaAnualController::class, 'perCapitaIndex'])->name('metaAnual.percapita.index')->middleware(['can:Acceso a Meta Anual']);
    Route::get('/metaAnual/percapita/create', [MetaAnualController::class, 'perCapitaCreate'])->name('metaAnual.percapita.create')->middleware(['can:Acceso a Meta Anual']);
    Route::post('/metaAnual/percapita', [MetaAnualController::class, 'perCapitaStore'])->name('metaAnual.percapita.store')->middleware(['can:Acceso a Meta Anual']);
    Route::get('/metaAnual/percapita/{id}/edit', [MetaAnualController::class, 'perCapitaEdit'])->name('metaAnual.percapita.edit')->middleware(['can:Acceso a Meta Anual']);
    Route::put('/metaAnual/percapita/{id}', [MetaAnualController::class, 'perCapitaUpdate'])->name('metaAnual.percapita.update')->middleware(['can:Acceso a Meta Anual']);
    Route::delete('/metaAnual/percapita/{id}', [MetaAnualController::class, 'perCapitaDestroy'])->name('metaAnual.percapita.destroy')->middleware(['can:Acceso a Meta Anual']);
    Route::get('/metaAnual/percapita/{id}', [MetaAnualController::class, 'perCapitaShow'])->name('metaAnual.percapita.show')->middleware(['can:Acceso a Meta Anual']);

    Route::get('/reporteDiario', [ReporteDiarioController::class, 'index'])->name('reporteDiario.index')->middleware(['can:Acceso a Inicio']);
    Route::post('/reporteDiario/generar', [ReporteDiarioController::class, 'generarReporte'])->name('reporteDiario.generar')->middleware(['can:Acceso a Inicio']);

    Route::get('/prediccionesZonas/obtenerPredicciones', [PrediccionesZonasController::class, 'obtenerPredicciones']);

    Route::resource('/prediccionesZonas', PrediccionesZonasController::class)->middleware(['can:Acceso a Predicciones']);

    Route::get('/indices-calidad/create/{tipo}', [IndicesCalidadController::class, 'create'])->name('indicesCalidad.create');
    Route::get('/indices-calidad/ver/{tipo}', [IndicesCalidadController::class, 'show'])->name('indicesCalidad.show');
    Route::get('/indices-calidad-search', [IndicesCalidadController::class, 'search'])->name('indicesCalidad.search');
    Route::get('/indices-calidad', [IndicesCalidadController::class, 'index'])->name('indicesCalidad.index');
    Route::resource('indices-calidad', IndicesCalidadController::class)->except(['index', 'show', 'create'])->names('indicesCalidad');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/graficas', function () {
        return view('graficas');
    })->name('graficas')->middleware(['can:Acceso a Graficas']);
    Route::get('/acerca-de', function () {
        return view('acerca-de');
    })->name('acerca-de');
});
