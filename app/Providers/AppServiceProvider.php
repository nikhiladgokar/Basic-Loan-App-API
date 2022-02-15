<?php

namespace App\Providers;

use App\Payment\StripePaymentGateway;
use App\Payment\PaymentGatewayContract;
use Illuminate\Support\ServiceProvider;
use App\Payment\BraintreePaymentGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaymentGatewayContract::class, function($app){

            $paymentGetway=request()->has('payment_getway')? request()->payment_getway:'';

            switch ($paymentGetway) {
                case "braintree":
                    return new BraintreePaymentGateway('usd');
                    break;
                default:
                    return new StripePaymentGateway('usd');
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
