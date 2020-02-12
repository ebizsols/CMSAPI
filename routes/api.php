<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('apiLogin', 'API\Auth\LoginController@index');
Route::post('resetPasswordToken', 'API\Auth\ForgotPasswordController@sendResetLinkEmail');
Route::post('resetPassword', 'API\Auth\ResetPasswordController@reset');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('getLoginInUserDetail', 'API\User\UserController@getLoginInUserDetail');
    Route::post('UserLeftMenu', 'API\Menus\UserLeftMenuController@index');
});

// Super admin routes
Route::group(
    ['namespace' => 'API\SuperAdmin', 'prefix' => 'super-admin', 'as' => 'super-admin.'], function () {

    Route::middleware('auth:api', 'throttle:rate_limit,1')->group(function () {

        //Package APIs
        Route::get('/Packages', 'SuperAdminPackageController@index');
        Route::post('/Package/CreatePackage', 'SuperAdminPackageController@store');
        Route::post('/Package/UpdatePackage', 'SuperAdminPackageController@update');
        Route::delete('/Package/Delete', 'SuperAdminPackageController@destroy');

        //Company APIs
        Route::get('/Companies', 'SuperAdminCompanyController@index');
        Route::post('/Company/CreateEditInfo', 'SuperAdminCompanyController@createEditData');
        Route::post('/Company/CreateCompany', 'SuperAdminCompanyController@store');
        Route::post('/Company/UpdateCompany', 'SuperAdminCompanyController@update');
        Route::post('/Company/AssignPackage', 'SuperAdminCompanyController@updatePackage');
        Route::delete('/Company/Delete', 'SuperAdminCompanyController@destroy');

        //SuperAdmin APIs
        Route::get('/SuperAdmin', 'SuperAdminController@index');
        Route::post('/SuperAdmin/StoreSuperAdmin', 'SuperAdminController@create');
        Route::post('/SuperAdmin/UpdateSuperAdmin', 'SuperAdminController@edit');
        Route::delete('/SuperAdmin/Delete', 'SuperAdminController@destroy');

        //currency APIs
        Route::get('/CurrencySettings', 'SuperAdminCurrencySettingController@index');
        Route::post('/CurrencySettings/CreateEditInfo', 'SuperAdminCurrencySettingController@createEditData');
        Route::post('/CurrencySetting/Store', 'SuperAdminCurrencySettingController@store');
        Route::post('/CurrencySetting/Update', 'SuperAdminCurrencySettingController@update');
        Route::delete('/CurrencySetting/Delete', 'SuperAdminCurrencySettingController@destroy');

        //Offline payment setting APIs
        Route::get('/OfflinePaymentSettings', 'OfflinePaymentSettingController@index');
        Route::post('/OfflinePaymentSetting/CreateEditInfo', 'OfflinePaymentSettingController@createEditData');
        Route::post('/OfflinePaymentSetting/Store', 'OfflinePaymentSettingController@store');
        Route::post('/OfflinePaymentSetting/Update', 'OfflinePaymentSettingController@update');
        Route::delete('/OfflinePaymentSetting/Delete', 'OfflinePaymentSettingController@destroy');

        //Online payment setting APIs
        Route::post('/OnlinePaymentSetting/CreateEditInfo', 'SuperAdminOnlinePaymentSettingsController@index');
        Route::post('/OnlinePaymentSetting/Update', 'SuperAdminOnlinePaymentSettingsController@update');
        Route::post('/OnlinePaymentSetting/ChangePaymentMethod', 'SuperAdminOnlinePaymentSettingsController@changePaymentMethod');

        // Invoice
        Route::get('/Invoices', 'SuperAdminInvoiceController@index');

        Route::get('/Dashboard', 'SuperAdminDashboardController@index');

        //Setting
        Route::get('/Setting', 'SuperAdminSettingsController@index');
        Route::post('/UpdateGlobalSetting', 'SuperAdminSettingsController@update');

        //Email Setting
        Route::get('/EmailSetting', 'SuperAdminEmailSettingsController@index');
        Route::post('/EmailSettingUpdate', 'SuperAdminEmailSettingsController@update');
        
        //Push Notification Setting
        Route::get('/PushNotification', 'SuperAdminPushSettingsController@index');
        Route::post('/PushNotificationUpdate', 'SuperAdminPushSettingsController@update');
        
        //Language Setting
        Route::get('/AllLanguages','SuperAdminLanguageSettingsController@index');
     
    });








    //Route::ApiResource('/Packages', 'SuperAdminPackageController');

    /*Route::get('/dashboard', 'SuperAdminDashboardController@index')->name('dashboard');
    Route::post('profile/updateOneSignalId', ['uses' => 'SuperAdminProfileController@updateOneSignalId'])->name('profile.updateOneSignalId');
    Route::resource('/profile', 'SuperAdminProfileController', ['only' => ['index', 'update']]);

    // Faq routes
    Route::resource('/faq-category/{category}/faq', 'SuperAdminFaqController')->except(['index', 'show']);

    // Faq Category routes
    Route::get('faq-category/data', ['uses' => 'SuperAdminFaqCategoryController@data'])->name('faq-category.data');
    Route::resource('/faq-category', 'SuperAdminFaqCategoryController');

    // Packages routes
    Route::get('packages/data', ['uses' => 'SuperAdminPackageController@data'])->name('packages.data');
    Route::resource('/packages', 'SuperAdminPackageController');

    // Companies routes
    Route::get('companies/data', ['uses' => 'SuperAdminCompanyController@data'])->name('companies.data');
    Route::get('companies/editPackage/{companyId}', ['uses' => 'SuperAdminCompanyController@editPackage'])->name('companies.edit-package.get');
    Route::put('companies/editPackage/{companyId}', ['uses' => 'SuperAdminCompanyController@updatePackage'])->name('companies.edit-package.post');
    Route::post('/companies', ['uses' => 'SuperAdminCompanyController@store']);

    Route::resource('/companies', 'SuperAdminCompanyController');
    Route::get('invoices/data', ['uses' => 'SuperAdminInvoiceController@data'])->name('invoices.data');
    Route::resource('/invoices', 'SuperAdminInvoiceController', ['only' => ['index']]);
    Route::get('paypal-invoice-download/{id}', array('as' => 'paypal.invoice-download','uses' => 'SuperAdminInvoiceController@paypalInvoiceDownload',));
    Route::get('billing/invoice-download/{invoice}', 'SuperAdminInvoiceController@download')->name('stripe.invoice-download');
    Route::get('billing/razorpay-download/{invoice}', 'SuperAdminInvoiceController@razorpayInvoiceDownload')->name('razorpay.invoice-download');

    Route::resource('/settings', 'SuperAdminSettingsController', ['only' => ['index', 'update']]);

    Route::get('super-admin/data', ['uses' => 'SuperAdminController@data'])->name('super-admin.data');
    Route::resource('/super-admin', 'SuperAdminController');

    Route::get('offline-plan/data', ['uses' => 'OfflinePlanChangeController@data'])->name('offline-plan.data');
    Route::post('offline-plan/verify', ['uses' => 'OfflinePlanChangeController@verify'])->name('offline-plan.verify');
    Route::post('offline-plan/reject', ['uses' => 'OfflinePlanChangeController@reject'])->name('offline-plan.reject');
    Route::resource('/offline-plan', 'OfflinePlanChangeController', ['only' => ['index', 'update']]);
    Route::group(
        ['prefix' => 'settings'],
        function () {
            Route::get('email-settings/sent-test-email', ['uses' => 'SuperAdminEmailSettingsController@sendTestEmail'])->name('email-settings.sendTestEmail');
            Route::resource('/email-settings', 'SuperAdminEmailSettingsController', ['only' => ['index', 'update']]);
            Route::post('/stripe-method-change', 'SuperAdminStripeSettingsController@changePaymentMethod')->name('stripe.method-change');
            Route::get('offline-payment-setting/createModal', ['uses' => 'OfflinePaymentSettingController@createModal'])->name('offline-payment-setting.createModal');
            Route::resource('offline-payment-setting', 'OfflinePaymentSettingController');
            Route::resource('/stripe-settings', 'SuperAdminStripeSettingsController', ['only' => ['index', 'update']]);

            Route::get('push-notification-settings/sent-test-notification', ['uses' => 'SuperAdminPushSettingsController@sendTestEmail'])->name('push-notification-settings.sendTestEmail');
            Route::get('push-notification-settings/sendTestNotification', ['uses' => 'SuperAdminPushSettingsController@sendTestNotification'])->name('push-notification-settings.sendTestNotification');
            Route::resource('/push-notification-settings', 'SuperAdminPushSettingsController', ['only' => ['index', 'update']]);

            Route::get('currency/exchange-key', ['uses' => 'SuperAdminCurrencySettingController@currencyExchangeKey'])->name('currency.exchange-key');
            Route::post('currency/exchange-key-store', ['uses' => 'SuperAdminCurrencySettingController@currencyExchangeKeyStore'])->name('currency.exchange-key-store');
            Route::resource('currency', 'SuperAdminCurrencySettingController');
            Route::get('currency/exchange-rate/{currency}', ['uses' => 'SuperAdminCurrencySettingController@exchangeRate'])->name('currency.exchange-rate');
            Route::get('currency/update/exchange-rates', ['uses' => 'SuperAdminCurrencySettingController@updateExchangeRate'])->name('currency.update-exchange-rates');
            Route::resource('currency', 'SuperAdminCurrencySettingController');

            Route::post('update-settings/deleteFile', ['uses' => 'UpdateDatabaseController@deleteFile'])->name('update-settings.deleteFile');
            Route::get('update-settings/install', ['uses' => 'UpdateDatabaseController@install'])->name('update-settings.install');
            Route::get('update-settings/manual-update', ['uses' => 'UpdateDatabaseController@manual'])->name('update-settings.manual');
            Route::resource('update-settings', 'UpdateDatabaseController');

            // Language Settings
            Route::post('language-settings/update-data/{id?}', ['uses' => 'SuperAdminLanguageSettingsController@updateData'])->name('language-settings.update-data');
            Route::resource('language-settings', 'SuperAdminLanguageSettingsController');

            Route::resource('front-settings', 'SuperAdminFrontSettingController', ['only' => ['index', 'update']]);
            Route::resource('package-settings', 'SuperAdminPackageSettingController', ['only' => ['index', 'update']]);

            Route::resource('feature-settings', 'SuperAdminFeatureSettingController');
            Route::resource('footer-settings', 'SuperAdminFooterSettingController');

        }
    );*/
});






