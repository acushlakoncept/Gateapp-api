<?php
use App\User;

//Authentication Routes ******************************************************
    //Registration
    Route::post('register/admin', 'Auth\RegisterController@admin');//has a role of 0

    Route::post('register/resident', 'Auth\RegisterController@resident');//has a role of 1

    Route::post('register/gateman', 'Auth\RegisterController@gateman');//has a role 2

    //forgot Password
    Route::post('phone/verify', 'Auth\ForgotPhoneController@verifyPhone');

    //Verify account
    Route::post('verify', 'Auth\VerificationController@verify');

    //Resend Token
    Route::get('resend/token', 'Auth\ForgotPhoneController@resedToken');

    //Login
    Route::post('login', 'Auth\LoginController@authenticate'); //Not Needed

    //Reset password for a new phone
    Route::put('phone/reset', 'Auth\ResetPhoneController@reset'); //Not  Needed


//Admin Routes (Specific Route)*******************************************************
Route::group(['middleware' => ['jwt.verify']], function() {
	//This is the route group every authenticated route with jwt token should go in here

    //(Admin interactions with User)
    
	//Show all user(this route is for only admin)(admin)
    Route::get('user/all', 'UserProfileController@all')->middleware('admin');

	//Show all user for a particular role(this route is for only admin)(admin)
    Route::get('user/all/{role_id}', 'UserProfileController@role')->middleware('admin');

    //show one admin
    Route::get('admin/{id}', 'UserProfileController@showOneAdmin')->middleware('admin');



    //(Admin interactions with Estates)

    //Admin only Update Estates by estate_id
    Route::put('/estate/edit/{id}', 'EstateController@update')->middleware('admin');

    //Delete Estates by estate_id
    Route::delete('/estate/delete/{id}', 'EstateController@deleteEstate')->middleware('admin');



    //(Admin interactions with Service Providers)

    //Admin only Create a service provider 
    Route::post('/service-provider', 'ServiceProviderController@create')->middleware('admin');

    //Admin only Update a service provider 
    Route::put('/service-provider/{id}', 'ServiceProviderController@update')->middleware('admin');

    //Admin only delete a specific service provider 
    Route::delete('/service-provider/{id}', 'ServiceProviderController@destroy')->middleware('admin');

    // Create a new Service Provider category
    Route::post('/sp-category', 'SPCategoryController@newCategory')->middleware('admin');

    // Edit a Service Provider category
    Route::put('sp-category/{id}', 'SPCategoryController@editCategory')->middleware('admin');

    // Delete a Service Provider category
    Route::delete('sp-category/{id}', 'SPCategoryController@deleteCategory')->middleware('admin');
});



// General Users Routes *******************************************************
Route::group(['middleware' => ['jwt.verify']], function() {
	//This is the route group every authenticated route with jwt token should go in here

     //Refresh token
    Route::post('/refresh', 'Auth\LoginController@refresh');

    //(User Profile)

    //Show active user i.e. current logged in user
    Route::get('/user', 'UserProfileController@index');

    //show one user
    Route::get('/user/{id}', 'UserProfileController@show');

    //Edit user ac count
    Route::put('/user/edit', 'UserProfileController@update');

    //Change Password
    Route::put('/user/phone', 'UserProfileController@password');

    //Delete user account
    Route::delete('/user/delete', 'UserProfileController@destroy');

    //Image upload api
    Route::post('user/image', 'UserProfileController@upload');                       


    //(Users interactions with Estates) Some of the estate route are controlled ny the admin

    //View All Estates
    Route::get('/estates', 'EstateController@index');

    //View Estates by city
    Route::get('/estate/city/{city}', 'EstateController@showCity');

    //View Estates by country
    Route::get('/estate/country/{country}', 'EstateController@showCountry');

    //View one Estates 
    Route::get('/estate/{id}', 'EstateController@show');

    //Get Estates by name
    Route::get('/estate/{name}', 'EstateController@search');

    //Create Estate
    Route::post('/estate', 'EstateController@store');

    //Select Estate
    Route::post('/estate/choose/{id}', 'EstateController@estateMemeber');



    //(Users Messging)

    //Get message
    Route::get('messages/{other_user_id}', 'MessageController@conversation');
    //Save Message
    Route::post('/messages', 'MessageController@saveMessage');  



    //(Users And ServiceProvider)

    //Get One
    Route::get('/service-provider/{id}', 'ServiceProviderController@show');

    //Get All Service Provider
    Route::get('/service-provider', 'ServiceProviderController@showAll');

    Route::get('/service-provider/category/{category_id}', 'ServiceProviderController@byCategory');

     // Get all Service Provider categories
     Route::get('/sp-category', 'SPCategoryController@fetchCategories');



    

    
});

// Logged in Residents Routes *******************************************************
Route::group(['middleware' => ['jwt.verify']], function() {
    //(Users Payment)

    //save payment
    Route::post('/payment', 'PaymentController@postPayment')->middleware('checkResident');

    //show all user payment
    Route::get('/payment/user/{user_id}', 'PaymentController@aUserPayment')->middleware('checkResident');

    //show payment
    Route::get('/payment/{id}', 'PaymentController@oneUniquePayment')->middleware('checkResident');



    //(Users Visitors)

    // Show all visitor
    Route::get('visitor', 'VisitorController@index')->middleware('checkResident');

    // Show single visitor
    Route::get('visitor/{id}', 'VisitorController@show')->middleware('checkResident');

    // Edit Visitor account
    Route::put('visitor/{id}', 'VisitorController@update')->middleware('checkResident');

    // Delete Visitor account
    Route::delete('visitor/{id}', 'VisitorController@destroy')->middleware('checkResident');

    //Create a visitor
    Route::post('visitor', 'VisitorController@store')->middleware('checkResident');

    //(Residents and Gateman)

    // Add a gateman 
    Route::post('resident/addGateman/{id}', 'ResidentController@addGateman')->middleware('checkResident');

    // remove a gateman by resident 
    Route::delete('resident/removeGateman/{id}', 'ResidentController@destroy')->middleware('checkResident');

    // Get gateman by phone
    Route::get('search/gateman/phone/{phone}', 'ResidentController@searchGatemanByPhone')->middleware('checkResident');

    // Get gateman by name
    Route::get('search/gateman/name/{name}', 'ResidentController@searchGatemanByName')->middleware('checkResident');

    // Show all pending gateman invitation
    Route::get('resident/pendingInvitation', 'ResidentController@viewPendingGateman')->middleware('checkResident');

    // Show accepted gateman invite
    Route::get('resident/acceptedInvitation', 'ResidentController@viewAcceptedGateman')->middleware('checkResident');
});

// Logged in Gateman Routes *******************************************************
Route::group(['middleware' => ['jwt.verify']], function() {
        // Get requests for a gateman
        Route::get('gateman/requests', 'GatemanController@residentRequest')->middleware('checkGateman');
    
        // Get list of visitors for gateman view
        Route::get('gateman/visitors', 'GatemanController@viewVisitors')->middleware('checkGateman');
    
    
        //Verify a visitor
        Route::put('gateman/admit', 'GatemanController@admitVisitor')->middleware('checkGateman');
    
        //Checkout visitor
        Route::put('gateman/checkout', 'GatemanController@visitor_out')->middleware('checkGateman');
    
        //gateman Accept/decline invitation 
        Route::put('gateman/response', 'GatemanController@response')->middleware('checkGateman');
    
       // Gateman accepts resident's requests route
        Route::put('gateman/requests/accept/{id}', 'GatemanController@accept')->middleware('checkGateman');
    
        // Gateman rejects resident's requests route
        Route::put('gateman/requests/reject/{id}', 'GatemanController@reject')->middleware('checkGateman');

        // Show all the residents a gateman works for
        Route::get('gateman/viewResidents', 'GatemanController@viewResidents')->middleware('checkGateman');


});



//This our testing api routes
Route::get('test', 'TestController@test');
Route::get('generate-code', 'TestController@qrCode');    
Route::post('test_image', 'TestController@upload');                       

// Route::get('init', function () {
//     event(new App\Events\notify('Someone'));
//     return "Notification sent";
// });
