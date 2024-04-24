
# .env Setup 
```
BKASH_USERNAME = 'your_bkash_provide_username'
BKASH_PASSWORD = 'your_bkash_provide_password'
BKASH_APP_KEY = 'your_bkash_provide_app_key'
BKASH_APP_SECRET ='your_bkash_provide_app_secret'

```
# web.php Setup
```
use App\Http\Controllers\BkashDynamicChargeController;

// Dynamic Charging (User)
Route::get('/bkash-pay', [BkashDynamicChargeController::class, 'payment'])->name('bkash-pay');
Route::post('/bkash-create', [BkashDynamicChargeController::class, 'createPayment'])->name('bkash-create');
Route::get('/bkash-dynamic-callback', [BkashDynamicChargeController::class, 'callback'])->name('bkash-dynamic-callback');

// Dynamic Charging (Admin)
Route::get('/bkash-refund', [BkashDynamicChargeController::class, 'getRefund'])->name('bkash-get-refund');
Route::post('/bkash-refund', [BkashDynamicChargeController::class, 'refundPayment'])->name('bkash-post-refund');
Route::get('/bkash-search', [BkashDynamicChargeController::class, 'getSearchTransaction'])->name('bkash-get-search');
Route::post('/bkash-search', [BkashDynamicChargeController::class, 'searchTransaction'])->name('bkash-post-search');

```
# Add Controller
```
Create a new Controller named 'BkashDynamicChargeController'
Controller Location --- App\Http\Controllers\BkashDynamicChargeController
You can now copy paste code from this project 'BkashDynamicChargeController' code

```

# Add Blades
```
Create a new view folder named 'bkash'
View Folder Loaction --- Resources\Views\bkash
--- Under 'bkash' folder create pay.blade.php  
--- Under 'bkash' folder create success.blade.php
--- Under 'bkash' folder create fail.blade.php
--- Under 'bkash' folder create refund.blade.php
Now you can copy paste code from this project
```
# Payment Test
```
Now run the application & go to '/bkash-pay' route
```
# Refund Test
```
Now run the application & go to '/bkash-refund' route
```
# Sandbox Testing Credentials 
```
Testing Number: 01619777283, 01619777283, 01823074817
OTP: 123456
PIN: 12121
```