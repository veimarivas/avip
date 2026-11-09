<?php

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\BienvenidosController;
use App\Http\Controllers\Backend\ClientesController;
use App\Http\Controllers\Backend\LocalidadesController;
use App\Http\Controllers\Backend\PedidosController;
use App\Http\Controllers\Backend\PermissionsController;
use App\Http\Controllers\Backend\RolePermissionsController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\UsersController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [BienvenidosController::class, 'principal'])->name('principal');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

///Admin Routes
Route::middleware(['auth', IsAdmin::class])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');
    // En el grupo de rutas de pedidos o en otro lugar apropiado
    Route::get('/admin/calendario/eventos', [AdminController::class, 'obtenerEventosCalendario'])->name('admin.calendario.eventos');

    // LOCALIDADES
    Route::controller(LocalidadesController::class)->group(function () {
        Route::get('/admin/localidades/listar', 'localidadesListar')->name('admin.localidades.listar');
        Route::post('/admin/localidades/registrar', 'localidadesRegistrar')->name('admin.localidades.registrar');
        Route::post('/admin/localidades/verificar', 'verificarNombre')->name('admin.localidades.verificar');
        Route::post('/admin/localidades/verificaredicion', 'verificarNombreEdicion')->name('admin.localidades.verificaredicion');
        Route::put('/admin/localidades/modificar', 'localidadesModificar')->name('admin.localidades.modificar');
        Route::delete('/admin/localidades/eliminar', 'localidadesEliminar')->name('admin.localidades.eliminar');
        Route::get('/admin/localidades/ver/{id}', 'localidadesVer')->name('admin.localidades.ver');

        // Nuevas rutas para gestión de productos
        Route::post('/admin/localidades/actualizar-precio', 'actualizarPrecioProducto')->name('admin.localidades.actualizar-precio');
        Route::post('/admin/localidades/agregar-producto', 'agregarProducto')->name('admin.localidades.agregar-producto');
        Route::post('/admin/localidades/eliminar-producto', 'eliminarProducto')->name('admin.localidades.eliminar-producto');

        // En web.php, dentro del grupo de rutas de localidades
        Route::post('/admin/localidades/actualizar-precios-masivo', 'actualizarPreciosMasivo')->name('admin.localidades.actualizar-precios-masivo');
    });

    // CLIENTES
    Route::controller(ClientesController::class)->group(function () {
        Route::get('/admin/clientes/listar', 'clientesListar')->name('admin.clientes.listar');
        Route::post('/admin/clientes/registrar', 'clientesRegistrar')->name('admin.clientes.registrar');
        Route::post('/admin/clientes/verificar-ci', 'verificarCi')->name('admin.clientes.verificar-ci');
        Route::post('/admin/clientes/verificar-ci-edicion', 'verificarCiEdicion')->name('admin.clientes.verificar-ci-edicion');
        Route::put('/admin/clientes/modificar', 'clientesModificar')->name('admin.clientes.modificar');
        Route::delete('/admin/clientes/eliminar', 'clientesEliminar')->name('admin.clientes.eliminar');
        Route::post('/admin/clientes/cambiar-estado', 'clienteCambiarEstado')->name('admin.clientes.cambiar-estado');
        Route::get('/admin/clientes/ver/{id}', 'clientesVer')->name('admin.clientes.ver');
        Route::get('/admin/clientes/por-localidad/{localidadId}', 'clientesPorLocalidad')->name('admin.clientes.por-localidad');

        // Nuevas rutas para gestión de pagos
        Route::get('/admin/clientes/pedidos-pendientes/{id}', 'obtenerPedidosPendientes')->name('admin.clientes.pedidos-pendientes');
        Route::post('/admin/clientes/pago-individual', 'realizarPagoIndividual')->name('admin.clientes.pago-individual');
        Route::post('/admin/clientes/pago-multiple', 'realizarPagoMultiple')->name('admin.clientes.pago-multiple');
        Route::post('/admin/clientes/pagar-completo', 'pagarPedidoCompleto')->name('admin.clientes.pagar-completo');


        // Rutas para gestión de orden
        Route::get('/admin/clientes/por-localidad-completa/{localidadId}', 'obtenerClientesPorLocalidad')->name('admin.clientes.por-localidad-completa');
        Route::post('/admin/clientes/actualizar-orden', 'actualizarOrdenClientes')->name('admin.clientes.actualizar-orden');

        Route::post('/admin/clientes/pago-individual-nuevo', 'realizarPagoIndividual')->name('admin.clientes.pago-individual-nuevo');
        Route::post('/admin/clientes/pago-multiple-nuevo',  'realizarPagoMultiple')->name('admin.clientes.pago-multiple-nuevo');
        Route::post('/admin/clientes/pago-con-descuento', 'realizarPagoConDescuento')->name('admin.clientes.pago-con-descuento');

        Route::get('/admin/recibos/{pago}/pdf', 'generarReciboPdf')->name('admin.recibos.pdf');
        Route::get('/admin/recibos/{pago}/ver', 'verRecibo')->name('admin.recibos.ver');

        Route::get('/admin/recibos/detalles', 'obtenerDetallesPago')->name('admin.recibos.detalles');

        Route::post('/admin/clientes/pago-multiple-priorizado', 'realizarPagoMultiplePriorizado')->name('admin.clientes.pago-multiple-priorizado');

        // En el grupo de rutas CLIENTES, agregar esta ruta:
        Route::get('/admin/clientes/ranking-deudas', [ClientesController::class, 'rankingDeudas'])->name('admin.clientes.ranking-deudas');
        Route::get('/admin/clientes/detalles-deuda/{id}', [ClientesController::class, 'obtenerDetallesDeuda'])->name('admin.clientes.detalles-deuda');
        Route::get('/admin/clientes/inactivos/{localidadId}', [ClientesController::class, 'obtenerClientesInactivos'])->name('admin.clientes.inactivos');
    });

    // PEDIDOS
    Route::controller(PedidosController::class)->group(function () {
        Route::post('/admin/pedidos/crear-para-localidad', 'crearPedidosParaLocalidad')->name('admin.pedidos.crear-para-localidad');
        Route::post('/admin/pedidos/verificar-existencia', 'verificarPedidosExistencia')->name('admin.pedidos.verificar-existencia');
        Route::get('/admin/pedidos', 'index')->name('admin.pedidos.index');
        Route::get('/admin/pedidos/{codigo}', 'verPorCodigo')->name('admin.pedidos.ver-por-codigo');
        Route::post('/admin/pedidos/{pedido}/registrar-productos', 'registrarProductos')->name('admin.pedidos.registrar-productos');
        Route::post('/admin/pedidos/{pedido}/procesar-productos', 'procesarProductos')->name('admin.pedidos.procesar-productos');
        Route::post('/admin/pedidos/{pedido}/generar-deuda', 'generarDeuda')->name('admin.pedidos.generar-deuda');
        // En el grupo de rutas PEDIDOS, agrega esta nueva ruta:
        Route::get('/admin/pedidos/{pedido}/registrar-form', 'mostrarFormularioRegistro')->name('admin.pedidos.registrar-form');

        // En el grupo de rutas PEDIDOS, agrega estas nuevas rutas:
        Route::get('/admin/pedidos/{pedido}/modificar-form', 'mostrarFormularioModificacion')->name('admin.pedidos.modificar-form');
        Route::get('/admin/pedidos/{pedido}/procesar-form', 'mostrarFormularioProcesamiento')->name('admin.pedidos.procesar-form');

        Route::post('/admin/pedidos/reporte-deudas-pdf', 'generarReporteDeudasPdf')->name('admin.pedidos.reporte-deudas-pdf');

        Route::post('/admin/pedidos/reporte-detalle-pedidos-pdf', 'generarReporteDetallePedidosPdf')->name('admin.pedidos.reporte-detalle-pedidos-pdf');
        Route::get('/admin/pedidos/{pedido}/reporte-individual-pdf', 'generarReporteIndividualPdf')->name('admin.pedidos.reporte-individual-pdf');

        Route::get('/admin/pedidos/reporte/consolidado', 'reporteConsolidado')->name('admin.pedidos.reporte-consolidado');

        Route::post('/admin/pedidos/reporte-consolidado-pdf-nuevo', 'generarReporteConsolidadoPdf')->name('admin.pedidos.reporte-consolidado-pdf-nuevo');
        Route::post('/admin/pedidos/reporte-consolidado-excel-nuevo', 'generarReporteConsolidadoExcel')->name('admin.pedidos.reporte-consolidado-excel-nuevo');

        // En el grupo de rutas PEDIDOS, reemplazar las rutas existentes:
        Route::post('/admin/pedidos/reporte-consolidado-pdf', 'generarReporteConsolidadoPdf')->name('admin.pedidos.reporte-consolidado-pdf');
        Route::post('/admin/pedidos/reporte-consolidado-excel', 'generarReporteConsolidadoExcel')->name('admin.pedidos.reporte-consolidado-excel');

        // Dentro del grupo de rutas PEDIDOS, agrega:
        Route::get('/admin/recibos/listar', [PedidosController::class, 'listarRecibos'])->name('admin.recibos.listar');

        // Control de ingresos
        Route::get('/admin/control-ingresos', [PedidosController::class, 'controlIngresos'])->name('admin.control-ingresos');
        Route::post('/admin/control-ingresos/exportar-pdf', [PedidosController::class, 'exportarControlIngresosPdf'])->name('admin.control-ingresos.exportar-pdf');
        Route::post('/admin/control-ingresos/exportar-excel', [PedidosController::class, 'exportarControlIngresosExcel'])->name('admin.control-ingresos.exportar-excel');

        Route::get('/admin/recibos/exportar-excel', [PedidosController::class, 'exportarRecibosExcel'])->name('admin.recibos.exportar-excel');

        // En el grupo de rutas PEDIDOS
        Route::post('/admin/recibos/obtener-detalles', [PedidosController::class, 'obtenerDetallesPago'])->name('admin.recibos.obtener-detalles');

        // En el grupo de rutas PEDIDOS
        Route::get('/admin/reportes/cobros-diarios', [PedidosController::class, 'reporteCobrosDiarios'])->name('admin.reportes.cobros-diarios');
        Route::post('/admin/reportes/cobros-diarios/detalles-usuario', [PedidosController::class, 'detallesCobrosUsuario'])->name('admin.reportes.cobros-diarios.detalles-usuario');

        Route::get('/admin/reportes/graficos-ingresos', [PedidosController::class, 'graficosIngresos'])->name('admin.reportes.graficos-ingresos');
        Route::post('/admin/reportes/graficos-ingresos/datos', [PedidosController::class, 'datosGraficosIngresos'])->name('admin.reportes.graficos-ingresos.datos');
        Route::post('/admin/reportes/graficos-ingresos/datos-usuario', [PedidosController::class, 'datosGraficosIngresosPorUsuario'])->name('admin.reportes.graficos-ingresos.datos-usuario');

        Route::get('/admin/pedidos/{pedido}/detalle-completo', [PedidosController::class, 'obtenerDetalleCompleto'])->name('admin.pedidos.detalle-completo');
    });



    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::controller(PermissionsController::class)->group(function () {
        Route::get('/admin/permissions/listar', 'permissionsListar')->name('admin.permissions.listar');
        Route::post('/admin/permissions/registrar', 'permissionsRegistrar')->name('admin.permissions.registrar');
        Route::post('/admin/permissions/verificar', 'verificarNombre')->name('admin.permissions.verificar');
        Route::post('/admin/permissions/verificaredicion', 'verificarNombreEdicion')->name('admin.permissions.verificaredicion');
        Route::post('/admin/permissions/modificar', 'permissionsModificar')->name('admin.permissions.modificar');
        Route::delete('/admin/permissions/eliminar', 'permissionsEliminar')->name('admin.permissions.eliminar');
        Route::get('/permissions/groups', 'getGroups')->name('admin.permissions.groups');
    });

    Route::controller(RolesController::class)->group(function () {
        Route::get('/admin/roles/listar', 'rolesListar')->name('admin.roles.listar');
        Route::post('/admin/roles/registrar', 'rolesRegistrar')->name('admin.roles.registrar');
        Route::post('/admin/roles/verificar', 'verificarNombre')->name('admin.roles.verificar');
        Route::post('/admin/roles/verificaredicion', 'verificarNombreEdicion')->name('admin.roles.verificaredicion');
        Route::post('/admin/roles/modificar', 'rolesModificar')->name('admin.roles.modificar');
        Route::delete('/admin/roles/eliminar', 'rolesEliminar')->name('admin.roles.eliminar');
    });

    Route::controller(RolePermissionsController::class)->group(function () {
        Route::get('/admin/role-permissions', 'index')->name('admin.role-permissions.index');
        Route::get('/admin/role-permissions/{id}', 'show')->name('admin.role-permissions.show');
        Route::post('/admin/role-permissions/assign', 'assignPermission')->name('admin.role-permissions.assign');
        Route::post('/admin/role-permissions/revoke', 'revokePermission')->name('admin.role-permissions.revoke');
    });

    Route::controller(UsersController::class)->group(function () {
        // Rutas para usuarios
        Route::post('/admin/users/registrar', [UsersController::class, 'registrar'])->name('admin.users.registrar');
        Route::post('/admin/users/eliminar', [UsersController::class, 'eliminar'])->name('admin.users.eliminar');
        Route::post('/admin/users/verificar-carnet', [UsersController::class, 'verificarCarnet'])->name('admin.users.verificar-carnet');
        Route::post('/admin/users/verificar-email', [UsersController::class, 'verificarEmail'])->name('admin.users.verificar-email');
        Route::post('/admin/users/actualizar', [UsersController::class, 'actualizar'])->name('admin.users.actualizar');
        Route::post('/admin/users/reset-password', [UsersController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::post('/admin/users/obtener-data', [UsersController::class, 'obtenerUserData'])->name('admin.users.obtener-data');
        Route::get('/admin/users/listar', [UsersController::class, 'listar'])->name('admin.users.listar');
    });
});

require __DIR__ . '/auth.php';
