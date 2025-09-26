<?php
// routes/api.php - Updated with missing routes

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CollectiveTripController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\PassengerController;
use App\Http\Controllers\Api\PassengerDashboardController;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\TripPassengerController;
use App\Http\Controllers\Api\UserLocationController;

// Rutas públicas
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/google', [AuthController::class, 'googleSignIn']);
// routes/api.php
Route::post('auth/complete-profile', [AuthController::class, 'completeProfile'])
    ->middleware('auth:api');

// Rutas protegidas
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);

        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Service Types
    Route::get('service-types', [ServiceTypeController::class, 'index']);

    // Trips (Passenger routes)
    Route::prefix('trips')->group(function () {
        Route::get('hasActiveTrip', [TripController::class, 'hasActiveTrip']);
        Route::post('estimate', [TripController::class, 'estimate']);
        Route::post('/', [TripController::class, 'store']);
        Route::get('active', [TripController::class, 'getActiveTrip']); // ✅ ADDED
        Route::get('current', [TripController::class, 'getCurrentTrip']); // ✅ ADDED (alias)
        Route::get('history', [TripController::class, 'getHistory']); // ✅ ADDED
        Route::get('{trip}', [TripController::class, 'show']);
        Route::put('{trip}/cancel', [TripController::class, 'cancel']);
        Route::post('{trip}/rate', [TripController::class, 'rate']);
    });

    // User Locations - Complete CRUD + utilities
    Route::prefix('user-locations')->group(function () {
        Route::get('/', [UserLocationController::class, 'index']);
        Route::post('/', [UserLocationController::class, 'store']);
        Route::put('{userLocation}', [UserLocationController::class, 'update']);
        Route::delete('{userLocation}', [UserLocationController::class, 'destroy']);

        // Utility endpoints
        Route::patch('{userLocation}/usage', [UserLocationController::class, 'updateUsage']);
        Route::get('frequent', [UserLocationController::class, 'frequent']);
        Route::get('most-used', [UserLocationController::class, 'mostUsed']);
        Route::get('search', [UserLocationController::class, 'search']);
        Route::get('stats', [UserLocationController::class, 'stats']);
    });

    // Driver routes
    Route::prefix('driver')->group(function () {
        // Driver Profile Management
        Route::get('profile', [DriverController::class, 'getProfile']);
        Route::post('profile/update', [DriverController::class, 'updateProfile']);
        Route::post('register', [AuthController::class, 'registerDriver']);
        // Vehicle Management
        Route::get('vehicles', [DriverController::class, 'getVehicles']);
        Route::post('vehicles/add', [DriverController::class, 'addVehicle']);
        Route::put('vehicles/{vehicle}', [DriverController::class, 'updateVehicle']);

        // Driver Status Management
        Route::post('status', [DriverController::class, 'updateStatus']);
        Route::get('status', [DriverController::class, 'getStatus']);

        // Location Management
        Route::post('location', [DriverController::class, 'updateLocation']);
        Route::get('location', [DriverController::class, 'getLocation']);

        // Statistics and Earnings
        Route::get('stats', [DriverController::class, 'getStats']);
        Route::get('earnings/records', [DriverController::class, 'records']);
        Route::get('earnings/summary', [DriverController::class, 'summary']);

        // Trip Requests Management
        Route::get('trip-requests', [DriverController::class, 'getTripRequests']);
        Route::post('trips/accept', [DriverController::class, 'acceptTrip']);
        Route::post('trips/reject', [DriverController::class, 'rejectTrip']);

        // Current Trip Management
        Route::get('trips/current', [DriverController::class, 'getCurrentTrip']);
        Route::put('trips/{trip}/status', [DriverController::class, 'updateTripStatus']);
        Route::post('trips/complete', [DriverController::class, 'completeTrip']);

        // Trip History
        Route::get('trips/history', [DriverController::class, 'getTripsHistory']);

        // Rating and Feedback
        //
        Route::post('trips/{trip}/rate', [DriverController::class, 'ratePassenger']);
        Route::post('trips/{trip}/incident', [DriverController::class, 'reportIncident']);
    });
});
Route::middleware('auth:api')->group(function () {

    Route::prefix('passenger')->group(function () {
        // Passenger Dashboard routes
        Route::get('stats', [PassengerDashboardController::class, 'getPassengerStats']);
        Route::get('recent-trips', [PassengerDashboardController::class, 'getPassengerRecentTrips']);
        Route::get('monthly-summary', [PassengerDashboardController::class, 'getPassengerMonthlySummary']);
        Route::get('frequent-locations', [PassengerDashboardController::class, 'getPassengerFrequentLocations']);
        Route::get('trip-patterns', [PassengerDashboardController::class, 'getPassengerTripPatterns']);
        //history routes
        Route::get('trip-history', [TripController::class, 'getHistory']);


        // Profile management (nuevo)
        Route::get('profile', [PassengerController::class, 'getProfile']);
        Route::post('profile/update', [PassengerController::class, 'updateProfile']);

        // Favorite locations management (nuevo)
        Route::get('saved-locations', [PassengerController::class, 'getSavedLocations']);
        Route::post('saved-locations', [PassengerController::class, 'addSavedLocation']);
        Route::delete('saved-locations/{id}', [PassengerController::class, 'deleteSavedLocation']);
    });
});

// routes/api.php
Route::middleware('auth:api')->group(function () {
    Route::prefix('collective-trips')->group(function () {
        Route::post('/', [CollectiveTripController::class, 'store']); // Crear viaje colectivo
        Route::get('/', [CollectiveTripController::class, 'index']); // Listar viajes colectivos activos
        Route::get('/active', [CollectiveTripController::class, 'active']); // Viajes colectivos activos
        Route::post('{trip}/start', [CollectiveTripController::class, 'start']); // Iniciar viaje
        Route::post('{trip}/complete', [CollectiveTripController::class, 'complete']); // Completar viaje
        Route::delete('{trip}', [CollectiveTripController::class, 'destroy']); // Cancelar viaje

        // Pasajeros
        Route::post('{trip}/passengers/reserve', [TripPassengerController::class, 'reserve']);
        Route::get('{trip}/passengers', [TripPassengerController::class, 'list']); // Para el conductor
        Route::get('{trip}/passengers/me', [TripPassengerController::class, 'me']); // Estado de mi reserva
        Route::post('{trip}/passengers/board', [TripPassengerController::class, 'board']); // Marcar como abordado
        Route::post('{trip}/passengers/drop', [TripPassengerController::class, 'drop']); // Marcar como bajado
        Route::post('{trip}/passengers/cancel', [TripPassengerController::class, 'cancel']); // Cancelar mi reserva
        Route::patch('{trip}/passengers/me', [TripPassengerController::class, 'update']); // Editar mi reserva

        Route::get('/passenger/active', [CollectiveTripController::class, 'passengerActive']);
    });
});


use App\Http\Controllers\Api\Admin\DashboardController;

Route::prefix('admin/dashboard')
    ->group(function () {

        // 1. Badges KPI
        Route::get('total-users',                  [DashboardController::class, 'totalUsers']);
        Route::get('total-passengers',             [DashboardController::class, 'totalPassengers']);
        Route::get('total-drivers',                [DashboardController::class, 'totalDrivers']);
        Route::get('total-trips',                  [DashboardController::class, 'totalTrips']);
        Route::get('total-revenue',                [DashboardController::class, 'totalRevenue']);
        Route::get('trips-today',                  [DashboardController::class, 'tripsToday']);
        Route::get('revenue-today',                [DashboardController::class, 'revenueToday']);

        // 2. Series temporales (start_date, end_date, granularity)
        Route::get('registrations-trend',          [DashboardController::class, 'registrationsTrend']);
        Route::get('trips-trend',                  [DashboardController::class, 'tripsTrend']);
        Route::get('revenue-trend',                [DashboardController::class, 'revenueTrend']);

        // 3. Segmentación y comparativos
        Route::get('trips-by-service-type',        [DashboardController::class, 'tripsByServiceType']);
        Route::get('cancellations-distribution',  [DashboardController::class, 'cancellationsDistribution']);

        // 4. Rankings
        Route::get('top-clients',                  [DashboardController::class, 'topClients']);
        Route::get('top-drivers',                  [DashboardController::class, 'topDrivers']);

        // 5. Predicción
        Route::get('predicted-revenue',            [DashboardController::class, 'predictedRevenue']);

        // 6. Alertas de baja actividad
        Route::get('alerts/low-activity',          [DashboardController::class, 'alertsLowActivity']);
    });

use App\Http\Controllers\Api\Admin\AdminCrmController;

Route::prefix('admin')->group(function () {

    // — Usuarios CRUD —
    Route::get    ('users',                   [AdminCrmController::class, 'indexUsers']);
    Route::post   ('users',                   [AdminCrmController::class, 'storeUser']);
    Route::get    ('users/{user}',            [AdminCrmController::class, 'showUser']);
    Route::put    ('users/{user}',            [AdminCrmController::class, 'updateUser']);
    Route::delete ('users/{user}',            [AdminCrmController::class, 'destroyUser']);

    // — Viajes: listar, ver, actualizar estado —
    Route::get    ('trips',                   [AdminCrmController::class, 'indexTrips']);
    Route::get    ('trips/{trip}',            [AdminCrmController::class, 'showTrip']);
    Route::put    ('trips/{trip}/status',     [AdminCrmController::class, 'updateTripStatus']);

    // — Conductores: aprobación / rechazo —
    Route::get    ('drivers/acceptation',     [AdminCrmController::class, 'pendingDrivers']);
    Route::post   ('drivers/{driver}/accept', [AdminCrmController::class, 'acceptDriver']);
    Route::post   ('drivers/{driver}/reject', [AdminCrmController::class, 'rejectDriver']);

});
