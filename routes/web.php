<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\DailyActivityController;
use App\Http\Controllers\DailyActivityFurtherController;
use App\Http\Controllers\DailyActivitySlaughterHouseController;
use App\Http\Controllers\DailyProductionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\OutsourcingController;
use App\Http\Controllers\PenggajianBoronganController;
use App\Http\Controllers\PenggajianHarianController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProcessTypeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\PsGroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WageConfigController;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthController::class, 'createLogin'])->name('login');
Route::post('/store', [AuthController::class, 'storeLogin'])->name('login.store');

Route::middleware(['auth'])->group(function() {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('password/edit', [AuthController::class, 'edit'])->name('password.edit');
    Route::put('password/update', [AuthController::class, 'update'])->name('password.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cost-centers/{departmentId}', [DashboardController::class, 'getCostCenters'])->name('dashboard.cost-centers');

    Route::get('attendance/groups-by-department', [AttendanceController::class, 'groupsByDepartment'])->name('admin.attendance.groups-by-department');
    Route::get('/cost-center-by-department/{departmentId}', [ProductController::class, 'costCenterByDepartment']);
    Route::get('/employee/cost-centers-by-department/{department}', [EmployeeController::class, 'getCostCenterByDepartment'])->name('admin.employee.cost-centers-by-department');
    Route::get('/employee/ps-groups/{costCenterId}', [EmployeeController::class, 'getPsGroups'])->name('employee.ps-group');
    Route::get('/daily-activity/cost-centers/{departmentId}', [DailyActivityController::class, 'getCostCenters'])->name('daily-activity.cost-centers');
    Route::get('/daily-activity/ps-groups/{costCenterId}', [DailyActivityController::class, 'getPsGroups'])->name('daily-activity.ps-groups');
    Route::get('/attendance/cost-centers/{departmentId}', [AttendanceController::class, 'getCostCenters'])->name('attendance.cost-centers');
    Route::get('/attendance/ps-groups/{costCenterId}', [AttendanceController::class, 'getPsGroups'])->name('attendance.ps-groups');
    Route::get('/daily-activity/employees/{costCenterId}/{psGroupId}', [DailyActivityController::class, 'getEmployees'])->name('daily-activity.employees');
    Route::get('/daily-production/cost-centers/{departmentId}', [DailyProductionController::class, 'getCostCenters'])->name('daily-production.cost-centers');
    Route::get('/daily-production/ps-groups/{costCenterId}', [DailyProductionController::class, 'getPsGroups'])->name('daily-production.ps-groups');
    Route::get('/daily-activity-further/products/{costCenterId}', [DailyActivityFurtherController::class, 'getProducts'])->name('admin-production.daily-activity-further.products');

    Route::get('/attendance/lines/{departmentId}', [AttendanceController::class, 'getLines'])->name('attendance.lines');
    Route::get('/daily-activity-further/ps-groups/{costCenterId}', [DailyActivityFurtherController::class, 'getPsGroups'])->name('daily-activity-further.ps-groups');

    Route::get('/daily-activity-further/employees/{costCenterId}/{psGroupId}', [DailyActivityFurtherController::class, 'getEmployees'])->name('daily-activity-further.employees');
    Route::get('/daily-activity-further/cost-centers/{departmentId}',  [DailyActivityFurtherController::class, 'getCostCenters'])->name('daily-activity-further.cost-centers');
    // Route::get('/employees/search', [EmployeeController::class, 'search']);

    Route::get('/daily-activity-slaughter-house/cost-centers/{departmentId}', [DailyActivitySlaughterHouseController::class, 'getCostCenters'])->name('daily-activity-slaughter-house.cost-centers');
    Route::get('/daily-activity-slaughter-house/ps-groups/{costCenterId}', [DailyActivitySlaughterHouseController::class, 'getPsGroups'])->name('daily-activity-slaughter-house.ps-groups');
    Route::get('/daily-activity-slaughter-house/products/{departmentIdId}', [DailyActivitySlaughterHouseController::class, 'getProducts'])->name('daily-activity-slaughter-house.products');
    Route::get('/daily-activity-slaughter-house/lines/{departmentId}', [DailyActivitySlaughterHouseController::class, 'getLines'])->name('daily-activity-slaughter-house.lines');
});


Route::prefix('admin-production')->middleware(['auth', 'role:Admin Production'])->group(function() {
    Route::prefix('employees')->group(function() {
        Route::get('/', [EmployeeController::class, 'index'])->name('admin-production.employee.index');
        // Route::get('/areas/{id}', [EmployeeController::class, 'detail'])->name('admin-production.employee.detail');
        Route::get('/create', [EmployeeController::class, 'create'])->name('admin-production.employee.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('admin-production.employee.store');
        Route::get('/import', [EmployeeController::class, 'importPage'])->name('admin-production.employee.import');
        Route::post('/upload', [EmployeeController::class, 'upload'])->name('admin-production.employee.upload');
    });

    Route::prefix('products')->group(function() {
        Route::get('/', [ProductController::class, 'index'])->name('admin-production.product.index');
        Route::get('/create', [ProductController::class, 'create'])->name('admin-production.product.create');
        Route::post('/store', [ProductController::class, 'store'])->name('admin-production.product.store');
        Route::get('{id}/edit', [ProductController::class, 'edit'])->name('admin-production.product.edit');
        Route::put('{id}/update', [ProductController::class, 'update'])->name('admin-production.product.update');
        Route::delete('{id}/delete', [ProductController::class, 'destroy'])->name('admin-production.product.destroy');
        Route::get('/import', [ProductController::class, 'importPage'])->name('admin-production.product.import');
        Route::post('/upload', [ProductController::class, 'upload'])->name('admin-production.product.upload');    
    });

    Route::prefix('products-further')->group(function() {
        Route::get('/', [ProductController::class, 'indexFurther'])->name('admin-production.product-further.index');
        Route::get('/create', [ProductController::class, 'createFurther'])->name('admin-production.product-further.create');
        Route::post('/store', [ProductController::class, 'storeFurther'])->name('admin-production.product-further.store');
        Route::get('{id}/edit', [ProductController::class, 'editFurther'])->name('admin-production.product-further.edit');
        Route::put('{id}/update', [ProductController::class, 'updateFurther'])->name('admin-production.product-further.update');
        Route::delete('{id}/delete', [ProductController::class, 'destroyFurther'])->name('admin-production.product-further.destroy');
        Route::get('/import', [ProductController::class, 'importPageFurther'])->name('admin-production.product-further.import');
        Route::post('/upload', [ProductController::class, 'uploadFurther'])->name('admin-production.product-further.upload');
    });

    Route::prefix('attendances')->group(function(){
        Route::get('/', [AttendanceController::class, 'index'])->name('admin-production.attendance.index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('admin-production.attendance.create');
        Route::get('/summary', [AttendanceController::class, 'summary'])->name('admin-production.attendance.summary');
        Route::get('/summary/{employee}/detail', [AttendanceController::class, 'detail'])->name('admin-production.attendance.summary.detail');
        // Route::post('/store', [EmployeeAttendanceController::class, 'store'])->name('admin-production.attendance.store');
        // Route::get('{id}/detail', [AttendanceController::class, 'detail'])->name('admin-production.attendance.detail');
        // Route::get('{id}/edit', [EmployeeAttendanceController::class, 'edit'])->name('admin-production.attendance.edit');
        // Route::put('{id}/update', [EmployeeAttendanceController::class, 'update'])->name('admin-production.attendance.update');
        // Route::delete('{id}/delete', [EmployeeAttendanceController::class, 'destroy'])->name('admin-production.attendance.destroy');
        Route::post('/bulk-store', [AttendanceController::class, 'bulkStore'])->name('admin-production.attendance.bulk.store');
        Route::get('/summary/export-excel', [AttendanceController::class, 'exportSummaryExcel'])->name('admin-production.attendance.summary.export-excel');
        Route::get('/summary/export-pdf', [AttendanceController::class, 'exportSummaryPdf'])->name('admin-production.attendance.summary.export-pdf');    
    });

    Route::prefix('daily-activity')->group(function() {
        Route::get('/', [DailyActivityController::class, 'index'])->name('admin-production.daily-activity.index');
        Route::get('/create', [DailyActivityController::class, 'create'])->name('admin-production.daily-activity.create');
        Route::post('/store', [DailyActivityController::class, 'store'])->name('admin-production.daily-activity.store');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyActivityController::class, 'detail'])->name('admin-production.daily-activity.detail');
        Route::get('/department/{department}/cost-centers', [DailyActivityController::class, 'getCostCenters'])->name('admin-production.daily-activity.cost-centers');
        Route::get('/cost-center/{costCenterId}/products', [DailyActivityController::class, 'getProducts'])->name('admin-production.daily-activity.products');
        Route::get('/cost-center/{costCenter}/ps-groups', [DailyActivityController::class, 'getPsGroups'])->name('admin-production.daily-activity.ps-groups');
        Route::get('/cost-center/{costCenter}/employees', [DailyActivityController::class, 'getEmployees'])->name('admin-production.daily-activity.employees');
        // Route::get('/import', [DailyActivityController::class, 'importPage'])->name('admin-production.daily-activity.import');
        // Route::post('/upload', [DailyActivityController::class, 'upload'])->name('admin-production.daily-activity.upload');    
        Route::get('/{id}/edit', [DailyActivityController::class, 'edit'])->name('admin-production.daily-activity.edit');
        Route::put('/{id}/update', [DailyActivityController::class, 'update'])->name('admin-production.daily-activity.update');
        Route::delete('/{id}/delete', [DailyActivityController::class, 'destroy'])->name('admin-production.daily-activity.destroy');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyActivityController::class, 'exportExcel'])->name('daily-activity.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyActivityController::class, 'exportPdf'])->name('daily-activity.export-pdf');    
    });

    Route::prefix('daily-production')->group(function() {
        Route::get('/', [DailyProductionController::class, 'index'])->name('admin-production.daily-production.index');
        Route::get('/create', [DailyProductionController::class, 'create'])->name('admin-production.daily-production.create');
        Route::post('/store', [DailyProductionController::class, 'store'])->name('admin-production.daily-production.store');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyProductionController::class, 'detail'])->name('admin-production.daily-production.detail');
        Route::get('/department/{department}/cost-centers', [DailyProductionController::class, 'getCostCenters'])->name('admin-production.daily-production.cost-centers');
        Route::get('/cost-center/{costCenterId}/products', [DailyProductionController::class, 'getProducts'])->name('admin-production.daily-production.products');
        Route::get('/cost-center/{costCenter}/ps-groups', [DailyProductionController::class, 'getPsGroups'])->name('admin-production.daily-production.ps-groups');
        Route::get('/{id}/edit', [DailyProductionController::class, 'edit'])->name('admin-production.daily-production.edit');
        Route::put('/{id}/update', [DailyProductionController::class, 'update'])->name('admin-production.daily-production.update');
        Route::delete('/{id}/delete', [DailyProductionController::class, 'destroy'])->name('admin-production.daily-production.destroy');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyProductionController::class, 'exportExcel'])->name('daily-production.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyProductionController::class, 'exportPdf'])->name('daily-production.export-pdf');
    });

    Route::prefix('daily-activity-further')->group(function () {

        Route::get('/', [DailyActivityFurtherController::class, 'index'])->name('admin-production.daily-activity-further.index');

        Route::get('/create', [DailyActivityFurtherController::class, 'create'])->name('admin-production.daily-activity-further.create');

        Route::post('/store', [DailyActivityFurtherController::class, 'store'])->name('admin-production.daily-activity-further.store');

        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail/{lineId?}', [DailyActivityFurtherController::class, 'detail'])->name('admin-production.daily-activity-further.detail');

        Route::get('/{id}/edit',[DailyActivityFurtherController::class, 'edit'])->name('admin-production.daily-activity-further.edit');

        Route::put('/{id}/update',[DailyActivityFurtherController::class, 'update'])->name('admin-production.daily-activity-further.update');

        Route::delete('/{id}/delete', [DailyActivityFurtherController::class, 'destroy'])->name('admin-production.daily-activity-further.destroy');

        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyActivityFurtherController::class, 'exportExcel'])->name('admin-production.daily-activity-further.export-excel');

        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyActivityFurtherController::class, 'exportPdf'])->name('admin-production.daily-activity-further.export-pdf');
    });

    Route::prefix('daily-activity-slaughter-house')->group(function () {
        Route::get('/', [DailyActivitySlaughterHouseController::class, 'index'])->name('admin-production.daily-activity-slaughter-house.index');
        Route::get('/create', [DailyActivitySlaughterHouseController::class, 'create'])->name('admin-production.daily-activity-slaughter-house.create');
        Route::post('/store', [DailyActivitySlaughterHouseController::class, 'store'])->name('admin-production.daily-activity-slaughter-house.store');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail/{lineId}',[DailyActivitySlaughterHouseController::class, 'detail'])->name('admin-production.daily-activity-slaughter-house.detail');
        Route::get('/{id}/edit', [DailyActivitySlaughterHouseController::class, 'edit'])->name('admin-production.daily-activity-slaughter-house.edit');
        Route::put('/{id}/update', [DailyActivitySlaughterHouseController::class, 'update'])->name('admin-production.daily-activity-slaughter-house.update');
        Route::delete('/{id}/delete', [DailyActivitySlaughterHouseController::class, 'destroy'])->name('admin-production.daily-activity-slaughter-house.destroy');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyActivitySlaughterHouseController::class, 'exportExcel'])->name('admin-production.daily-activity-slaughter-house.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf',[DailyActivitySlaughterHouseController::class, 'exportPdf'])->name('admin-production.daily-activity-slaughter-house.export-pdf');

    });

    Route::prefix('penggajian-harian')->group(function () {
        Route::get('/', [PenggajianHarianController::class, 'index'])->name('admin-production.penggajian-harian.index');
        Route::get('/export-excel', [PenggajianHarianController::class, 'exportExcel'])->name('admin-production.penggajian-harian.export-excel');
        Route::get('/export-pdf', [PenggajianHarianController::class, 'exportPdf'])->name('admin-production.penggajian-harian.export-pdf');
    });

    Route::prefix('penggajian-borongan')->group(function () {
        Route::get('/', [PenggajianBoronganController::class, 'index'])->name('admin-production.penggajian-borongan.index');
        Route::get('/export-pdf', [PenggajianBoronganController::class, 'exportPdf'])->name('admin-production.penggajian-borongan.export-pdf');
        Route::get('/export-excel', [PenggajianBoronganController::class, 'exportExcel'])->name('admin-production.penggajian-borongan.export-excel');    
    });
});

Route::prefix('admin')->middleware(['auth', 'role:Admin'])->group(function() {
    Route::prefix('outsourcings')->group(function() {
        Route::get('/', [OutsourcingController::class, 'index'])->name('admin.outsourcing.index');
        Route::get('/create', [OutsourcingController::class, 'create'])->name('admin.outsourcing.create');
        Route::post('/store', [OutsourcingController::class, 'store'])->name('admin.outsourcing.store');
        Route::get('{id}/edit', [OutsourcingController::class, 'edit'])->name('admin.outsourcing.edit');
        Route::put('{id}/update', [OutsourcingController::class, 'update'])->name('admin.outsourcing.update');
        Route::delete('{id}/delete', [OutsourcingController::class, 'destroy'])->name('admin.outsourcing.destroy');
    });

    Route::prefix('product-groups')->name('admin.product-group.')->group(function () {
        Route::get('/', [ProductGroupController::class, 'index'])->name('index');
        Route::get('/create', [ProductGroupController::class, 'create'])->name('create');
        Route::post('/', [ProductGroupController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ProductGroupController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProductGroupController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductGroupController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('departments')->group(function() {
        Route::get('/', [DepartmentController::class, 'index'])->name('department.index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('department.create');
        Route::post('/store', [DepartmentController::class, 'store'])->name('department.store');
        Route::get('{id}/edit', [DepartmentController::class, 'edit'])->name('department.edit');
        Route::put('{id}/update', [DepartmentController::class, 'update'])->name('department.update');
        Route::delete('{id}/delete', [DepartmentController::class, 'destroy'])->name('department.destroy');
    });

    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
        Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
        Route::post('/store', [UserController::class, 'store'])->name('admin.user.store');
        Route::get('{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
        Route::put('{id}/update', [UserController::class, 'update'])->name('admin.user.update');
        Route::delete('{id}/delete', [UserController::class, 'destroy'])->name('admin.user.destroy');
    });

    Route::prefix('cost-centers')->group(function() {
        Route::get('/', [CostCenterController::class, 'index'])->name('admin.cost-center.index');
        Route::get('/create', [CostCenterController::class, 'create'])->name('admin.cost-center.create');
        Route::post('/store', [CostCenterController::class, 'store'])->name('admin.cost-center.store');
        Route::get('{id}/edit', [CostCenterController::class, 'edit'])->name('admin.cost-center.edit');
        Route::put('{id}/update', [CostCenterController::class, 'update'])->name('admin.cost-center.update');
        Route::delete('{id}/delete', [CostCenterController::class, 'destroy'])->name('admin.cost-center.destroy');
    });

    Route::prefix('groups')->group(function() {
        Route::get('/', [PsGroupController::class, 'index'])->name('admin.ps-group.index');
        Route::get('/create', [PsGroupController::class, 'create'])->name('admin.ps-group.create');
        Route::post('/store', [PsGroupController::class, 'store'])->name('admin.ps-group.store');
        Route::get('{id}/edit', [PsGroupController::class, 'edit'])->name('admin.ps-group.edit');
        Route::put('{id}/update', [PsGroupController::class, 'update'])->name('admin.ps-group.update');
        Route::delete('{id}/delete', [PsGroupController::class, 'destroy'])->name('admin.ps-group.destroy');
    });

    Route::prefix('lines')->group(function() {
        Route::get('/', [LineController::class, 'index'])->name('admin.line.index');
        Route::get('/create', [LineController::class, 'create'])->name('admin.line.create');
        Route::post('/store', [LineController::class, 'store'])->name('admin.line.store');
        Route::get('/{id}/edit', [LineController::class, 'edit'])->name('admin.line.edit');
        Route::put('/{id}/update', [LineController::class, 'update'])->name('admin.line.update');
        Route::delete('/{id}/delete', [LineController::class, 'destroy'])->name('admin.line.destroy');
    });

    Route::prefix('positions')->group(function() {
        Route::get('/', [PositionController::class, 'index'])->name('position.index');
        Route::get('/create', [PositionController::class, 'create'])->name('position.create');
        Route::post('/store', [PositionController::class, 'store'])->name('position.store');
        Route::get('{id}/edit', [PositionController::class, 'edit'])->name('position.edit');
        Route::put('{id}/update', [PositionController::class, 'update'])->name('position.update');
        Route::delete('{id}/delete', [PositionController::class, 'destroy'])->name('position.destroy');
    });

    Route::prefix('employees')->group(function() {
        Route::get('/', [EmployeeController::class, 'index'])->name('admin.employee.index');
        // Route::get('/areas/{id}', [EmployeeController::class, 'detail'])->name('admin.employee.detail');
        Route::get('/create', [EmployeeController::class, 'create'])->name('admin.employee.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('admin.employee.store');
        Route::get('{id}/detail', [EmployeeController::class, 'detail'])->name('admin.employee.detail');
        Route::get('{id}/edit', [EmployeeController::class, 'edit'])->name('admin.employee.edit');
        Route::put('{id}/update', [EmployeeController::class, 'update'])->name('admin.employee.update');
        Route::delete('{id}/delete', [EmployeeController::class, 'destroy'])->name('admin.employee.destroy');
        Route::get('/import', [EmployeeController::class, 'importPage'])->name('admin.employee.import');
        Route::post('/upload', [EmployeeController::class, 'upload'])->name('admin.employee.upload');
    });

    Route::prefix('process-types')->group(function() {
        Route::get('/', [ProcessTypeController::class, 'index'])->name('admin.process-type.index');
        Route::get('/create', [ProcessTypeController::class, 'create'])->name('admin.process-type.create');
        Route::post('/store', [ProcessTypeController::class, 'store'])->name('admin.process-type.store');
        Route::get('{id}/edit', [ProcessTypeController::class, 'edit'])->name('admin.process-type.edit');
        Route::put('{id}/update', [ProcessTypeController::class, 'update'])->name('admin.process-type.update');
        Route::delete('{id}/delete', [ProcessTypeController::class, 'destroy'])->name('admin.process-type.destroy');
    });

    Route::prefix('wage-config')->group(function () {
        Route::get('/', [WageConfigController::class, 'index'])->name('admin.wage-config.index');
        Route::get('/create', [WageConfigController::class, 'create'])->name('admin.wage-config.create');
        Route::post('/store', [WageConfigController::class, 'store'])->name('admin.wage-config.store');
        Route::get('/{id}/edit', [WageConfigController::class, 'edit'])->name('admin.wage-config.edit');
        Route::put('/{id}/update', [WageConfigController::class, 'update'])->name('admin.wage-config.update');
        Route::delete('/{id}/delete', [WageConfigController::class, 'destroy'])->name('admin.wage-config.destroy');
    });

    // Route::prefix('payrolls')->group(function () {
        // Route::prefix('harian')->group(function () {
        //     Route::get('/', [PayrollHarianController::class, 'getAllByAdmin'])->name('admin.payroll.harian.index');
        //     Route::get('/preview', [PayrollHarianController::class, 'preview'])->name('admin.payroll.harian.preview');
        //     Route::get('/detail/{month}/{year}', [PayrollHarianController::class, 'getDetailByAdmin'])->name('admin.payroll.harian.detail');
        //     Route::get('{id}/show', [PayrollHarianController::class, 'show'])->name('admin.payroll.harian.show');
        //     Route::get('/generate', [PayrollHarianController::class, 'generateForm'])->name('admin.payroll.harian.generate.form');
        //     Route::post('/generate', [PayrollHarianController::class, 'generate'])->name('admin.payroll.harian.generate');
        //     Route::patch('{id}/finalize', [PayrollHarianController::class, 'finalize'])->name('admin.payroll.harian.finalize');
        //     Route::patch('/finalize/{month}/{year}', [PayrollHarianController::class, 'finalizePeriod'])->name('admin.payroll.harian.finalize.period');
        //     Route::delete('{id}/delete', [PayrollHarianController::class, 'destroy'])->name('admin.payroll.harian.destroy');
        //     Route::delete('/destroy/{month}/{year}', [PayrollHarianController::class, 'destroyPeriod'])->name('admin.payroll.harian.destroy.period');
        //     Route::get('/export', [PayrollHarianController::class, 'export'])->name('admin.payroll.harian.export');
        //     Route::get('{id}/print', [PayrollHarianController::class, 'print'])->name('admin.payroll.harian.print');
        // });

    //     Route::prefix('borongan')->group(function () {
    //         Route::get('/', [PayrollBoronganController::class, 'index'])->name('admin.payroll.borongan.index');
    //         Route::get('/preview', [PayrollBoronganController::class, 'preview'])->name('admin.payroll.borongan.preview');
    //         Route::get('/detail/{month}/{year}', [PayrollBoronganController::class, 'detail'])->name('admin.payroll.borongan.detail');
    //         Route::get('{id}/show', [PayrollBoronganController::class, 'show'])->name('admin.payroll.borongan.show');
    //         Route::get('/generate', [PayrollBoronganController::class, 'generateForm'])->name('admin.payroll.borongan.generate.form');
    //         Route::post('/generate', [PayrollBoronganController::class, 'generate'])->name('admin.payroll.borongan.generate');
    //         Route::patch('{id}/finalize', [PayrollBoronganController::class, 'finalize'])->name('admin.payroll.borongan.finalize');
    //         Route::patch('/finalize/{month}/{year}', [PayrollBoronganController::class, 'finalizePeriod'])->name('admin.payroll.borongan.finalize.period');
    //         Route::delete('{id}/delete', [PayrollBoronganController::class, 'destroy']) ->name('admin.payroll.borongan.destroy');
    //         Route::delete('/destroy/{month}/{year}', [PayrollBoronganController::class, 'destroyPeriod'])->name('admin.payroll.borongan.destroy.period');
    //         Route::get('/export', [PayrollBoronganController::class, 'export'])->name('admin.payroll.borongan.export');
    //         Route::get('{id}/print', [PayrollBoronganController::class, 'print'])->name('admin.payroll.borongan.print');
    //     });
    // });
});

Route::prefix('general-manager')->middleware(['auth', 'role:General Manager'])->group(function() {
    Route::prefix('outsourcings')->group(function() {
        Route::get('/', [OutsourcingController::class, 'generalManagerIndex'])->name('general-manager.outsourcing.index');
    });

    Route::prefix('departments')->group(function() {
        Route::get('/', [DepartmentController::class, 'generalManagerIndex'])->name('general-manager.department.index');
    });

    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'generalManagerIndex'])->name('general-manager.user.index');
    });

    Route::prefix('cost-centers')->group(function() {
        Route::get('/', [CostCenterController::class, 'generalManagerIndex'])->name('general-manager.cost-center.index');
    });

    Route::prefix('groups')->group(function() {
        Route::get('/', [PsGroupController::class, 'generalManagerIndex'])->name('general-manager.ps-group.index');
    });


    Route::prefix('positions')->group(function() {
        Route::get('/', [PositionController::class, 'generalManagerIndex'])->name('general-manager.position.index');
    });

    Route::prefix('employees')->group(function() {
        Route::get('/', [EmployeeController::class, 'generalManagerIndex'])->name('general-manager.employee.index');
    });

    Route::prefix('attendances')->group(function(){
        Route::get('/', [AttendanceController::class, 'generalManagerIndex'])->name('general-manager.attendance.index');
        Route::get('/create', [AttendanceController::class, 'generalManagerCreate'])->name('general-manager.attendance.create');
        Route::get('/summary', [AttendanceController::class, 'generalManagerSummary'])->name('general-manager.attendance.summary');
        Route::get('/summary/{employee}/detail', [AttendanceController::class, 'generalManagerDetail'])->name('general-manager.attendance.summary.detail');
        Route::post('/bulk-store', [AttendanceController::class, 'generalManagerBulkStore'])->name('general-manager.attendance.bulk.store');
        Route::get('summary/export-excel', [AttendanceController::class, 'exportSummaryExcelGeneralManager'])->name('general-manager.attendance.summary.export-excel');
        Route::get('summary/export-pdf', [AttendanceController::class, 'exportSummaryPdfGeneralManager'])->name('general-manager.attendance.summary.export-pdf');
    
    });

    Route::prefix('daily-activity')->group(function() {
        Route::get('/', [DailyActivityController::class, 'generalManagerIndex'])->name('general-manager.daily-activity.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyActivityController::class, 'generalManagerDetail'])->name('general-manager.daily-activity.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyActivityController::class, 'exportExcelGeneralManager'])->name('general-manager.daily-activity.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyActivityController::class, 'exportPdfGeneralManager'])->name('general-manager.daily-activity.export-pdf');        
    });

    Route::prefix('daily-production')->group(function () {
        Route::get('/', [DailyProductionController::class, 'generalManagerIndex'])->name('general-manager.daily-production.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyProductionController::class, 'generalManagerDetail'])->name('general-manager.daily-production.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyProductionController::class, 'exportExcelGeneralManager'])->name('general-manager.daily-production.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyProductionController::class, 'exportPdfGeneralManager'])->name('general-manager.daily-production.export-pdf');
    });

    Route::prefix('daily-activity-further')->group(function() {
        Route::get('/',  [DailyActivityFurtherController::class,'generalManagerIndex'])->name('general-manager.daily-activity-further.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail/{lineId}',  [DailyActivityFurtherController::class,'generalManagerDetail'])->name('general-manager.daily-activity-further.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel',  [DailyActivityFurtherController::class,'exportExcelGeneralManager'])->name('general-manager.daily-activity-further.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf',  [DailyActivityFurtherController::class,'exportPdfGeneralManager'])->name('general-manager.daily-activity-further.export-pdf');
    });

    Route::prefix('daily-activity-slaughter-house')->group(function () {
        Route::get('/', [DailyActivitySlaughterHouseController::class, 'generalManagerIndex'])->name('general-manager.daily-activity-slaughter-house.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyActivitySlaughterHouseController::class, 'generalManagerDetail'])->name('general-manager.daily-activity-slaughter-house.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyActivitySlaughterHouseController::class, 'exportExcelGeneralManager'])->name('general-manager.daily-activity-slaughter-house.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyActivitySlaughterHouseController::class, 'exportPdfGeneralManager'])->name('general-manager.daily-activity-slaughter-house.export-pdf');

    });
    
    Route::prefix('penggajian-harian')->group(function () {
        Route::get('/', [PenggajianHarianController::class, 'generalManagerIndex'])->name('general-manager.penggajian-harian.index');
        Route::get('/export-excel', [PenggajianHarianController::class, 'exportExcelGeneralManager'])->name('general-manager.penggajian-harian.export-excel');
        Route::get('/export-pdf', [PenggajianHarianController::class, 'exportPdfGeneralManager'])->name('general-manager.penggajian-harian.export-pdf');
    });

    Route::prefix('penggajian-borongan')->group(function () {
        Route::get('/', [PenggajianBoronganController::class, 'generalManagerIndex'])->name('general-manager.penggajian-borongan.index');
        Route::get('/export-pdf', [PenggajianBoronganController::class, 'exportPdfGeneralManager'])->name('general-manager.penggajian-borongan.export-pdf');
        Route::get('/export-excel', [PenggajianBoronganController::class, 'exportExcelGeneralManager'])->name('general-manager.penggajian-borongan.export-excel');    
    });
});

Route::prefix('manager')->middleware(['auth', 'role:Manager'])->group(function() {
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'managerIndex'])->name('manager.employee.index');
        Route::get('/create', [EmployeeController::class, 'managerCreate'])->name('manager.employee.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('manager.employee.store');
        Route::get('{id}/detail', [EmployeeController::class, 'managerDetail'])->name('manager.employee.detail');
        Route::get('{id}/edit', [EmployeeController::class, 'managerEdit'])->name('manager.employee.edit');
        Route::put('{id}/update', [EmployeeController::class, 'update'])->name('manager.employee.update');
        Route::delete('{id}/delete', [EmployeeController::class, 'destroy'])->name('manager.employee.destroy');
    });
    Route::prefix('attendances')->group(function(){
        Route::get('/', [AttendanceController::class, 'managerIndex'])->name('manager.attendance.index');
        Route::get('/create', [AttendanceController::class, 'managerCreate'])->name('manager.attendance.create');
        Route::get('/summary', [AttendanceController::class, 'managerSummary'])->name('manager.attendance.summary');
        Route::get('/summary/{employee}/detail', [AttendanceController::class, 'managerDetail'])->name('manager.attendance.summary.detail');
        Route::post('/bulk-store', [AttendanceController::class, 'managerBulkStore'])->name('manager.attendance.bulk.store');
        Route::get('summary/export-excel', [AttendanceController::class, 'exportSummaryExcel'])->name('manager.attendance.summary.export-excel');
        Route::get('summary/export-pdf', [AttendanceController::class, 'exportSummaryPdfManager'])->name('manager.attendance.summary.export-pdf');

    });

    Route::prefix('daily-activity')->group(function() {
        Route::get('/', [DailyActivityController::class, 'managerIndex'])->name('manager.daily-activity.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyActivityController::class, 'managerDetail'])->name('manager.daily-activity.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyActivityController::class, 'exportExcelManager'])->name('manager.daily-activity.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyActivityController::class, 'exportPdfManager'])->name('manager.daily-activity.export-pdf');        
    });

    Route::prefix('daily-production')->group(function () {
        Route::get('/', [DailyProductionController::class, 'managerIndex'])->name('manager.daily-production.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyProductionController::class, 'managerDetail'])->name('manager.daily-production.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyProductionController::class, 'exportExcelManager'])->name('manager.daily-production.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyProductionController::class, 'exportPdfManager'])->name('manager.daily-production.export-pdf');
    });

    Route::prefix('daily-activity-further')->group(function() {
        Route::get('/',  [DailyActivityFurtherController::class,'managerIndex'])->name('manager.daily-activity-further.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail/{lineId}',  [DailyActivityFurtherController::class,'managerDetail'])->name('manager.daily-activity-further.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel',  [DailyActivityFurtherController::class,'exportExcelManager'])->name('manager.daily-activity-further.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf',  [DailyActivityFurtherController::class,'exportPdfManager'])->name('manager.daily-activity-further.export-pdf');
    });
    
    Route::prefix('daily-activity-slaughter-house')->group(function () {
        Route::get('/', [DailyActivitySlaughterHouseController::class, 'managerIndex'])->name('manager.daily-activity-slaughter-house.index');
        Route::get('/cost-center/{costCenter}/ps-group/{psGroup}/detail', [DailyActivitySlaughterHouseController::class, 'managerDetail'])->name('manager.daily-activity-slaughter-house.detail');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-excel', [DailyActivitySlaughterHouseController::class, 'exportExcelManager'])->name('manager.daily-activity-slaughter-house.export-excel');
        Route::get('/cost-center/{costCenterId}/ps-group/{psGroupId}/export-pdf', [DailyActivitySlaughterHouseController::class, 'exportPdfManager'])->name('manager.daily-activity-slaughter-house.export-pdf');
    });

    Route::prefix('penggajian-harian')->group(function () {
        Route::get('/', [PenggajianHarianController::class, 'ManagerIndex'])->name('manager.penggajian-harian.index');
        Route::get('/export-excel', [PenggajianHarianController::class, 'exportExcelManager'])->name('manager.penggajian-harian.export-excel');
        Route::get('/export-pdf', [PenggajianHarianController::class, 'exportPdfManager'])->name('manager.penggajian-harian.export-pdf');
    });

    Route::prefix('penggajian-borongan')->group(function () {
        Route::get('/', [PenggajianBoronganController::class, 'managerIndex'])->name('manager.penggajian-borongan.index');
        Route::get('/export-pdf', [PenggajianBoronganController::class, 'exportPdfManager'])->name('manager.penggajian-borongan.export-pdf');
        Route::get('/export-excel', [PenggajianBoronganController::class, 'exportExcelManager'])->name('manager.penggajian-borongan.export-excel');    
    });
});
