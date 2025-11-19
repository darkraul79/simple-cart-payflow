# Simple Cart & PayFlow

<p align="center">
  <strong>Unified e-commerce solution: Shopping Cart + Multi-Gateway Payments for Laravel</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/darkraul79/simple-cart-payflow"><img src="https://img.shields.io/packagist/v/darkraul79/simple-cart-payflow" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/darkraul79/simple-cart-payflow"><img src="https://img.shields.io/packagist/dt/darkraul79/simple-cart-payflow" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/darkraul79/simple-cart-payflow"><img src="https://img.shields.io/packagist/l/darkraul79/simple-cart-payflow" alt="License"></a>
</p>

> ⚠️ **Alpha Version (0.1.x)** - This meta-package combines Cartify and Payflow. APIs may change. Use with caution in
> production.

---

## What is Simple Cart & PayFlow?

**Simple Cart & PayFlow** is a **meta-package** that provides a unified installation and configuration for two powerful
Laravel packages:

- 🛒 **[Cartify](https://github.com/darkraul79/cartify)** - Flexible shopping cart with session & database persistence
- 💳 **[Payflow](https://github.com/darkraul79/payflow)** - Multi-gateway payment processor (Redsys, Stripe, PayPal)

Instead of installing and configuring both packages separately, you can install this single package and get everything
configured out of the box.

---

## Why Use This Package?

✅ **One command installation** - Get cart + payments in seconds  
✅ **Unified configuration** - Configure both packages from one config file  
✅ **Pre-configured integration** - Cart and payment work together seamlessly  
✅ **Independent updates** - Each underlying package can be updated independently  
✅ **Modular architecture** - Can still use Cartify or Payflow separately if needed

---

## Features

### From Cartify (Shopping Cart)

- ✅ Simple and intuitive API
- ✅ Automatic price calculations (subtotal, tax, total)
- ✅ Multiple cart instances (cart, wishlist, etc.)
- ✅ Persistent cart for authenticated users
- ✅ Session-based storage
- ✅ Database migrations included

### From Payflow (Payment Gateway)

- ✅ Unified API for multiple payment gateways
- ✅ **Redsys fully implemented** (Spain's leading payment gateway)
- ✅ Bizum support (instant mobile payments)
- ✅ Recurring payments
- ✅ Automatic signature verification
- ✅ Transaction logging
- ✅ Refund management

### Integrated Features

- ✅ Auto-clear cart on successful payment (configurable)
- ✅ Store cart snapshot with orders (configurable)
- ✅ Single configuration file for both packages

---

## Requirements

- PHP ^8.2 or ^8.3
- Laravel ^12.0

---

## Installation

Install via Composer:

```bash
composer require darkraul79/simple-cart-payflow
```

This will automatically install both `darkraul79/cartify` and `darkraul79/payflow`.

Publish the configuration files:

```bash
php artisan vendor:publish --provider="Darkraul79\SimpleCartPayFlow\SimpleCartPayFlowServiceProvider"
```

Or publish individual package configurations:

```bash
# Publish Cartify config and migrations
php artisan vendor:publish --provider="Darkraul79\Cartify\CartifyServiceProvider"

# Publish Payflow config
php artisan vendor:publish --provider="Darkraul79\Payflow\PayflowServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

---

## Configuration

Add to your `.env` file:

```env
# Cart Configuration
CARTIFY_TAX_RATE=0.21
CARTIFY_CURRENCY=EUR
CARTIFY_CURRENCY_SYMBOL=€

# Payment Configuration
PAYMENT_GATEWAY_DEFAULT=redsys

# Redsys Gateway
REDSYS_KEY=your-secret-key
REDSYS_MERCHANT_CODE=your-merchant-code
REDSYS_TERMINAL=1
REDSYS_CURRENCY=978
REDSYS_ENVIRONMENT=test
REDSYS_TRADE_NAME="Your Store"

# Integration Options
AUTO_CLEAR_CART_ON_SUCCESS=true
STORE_CART_WITH_ORDER=true
```

---

## Quick Start

### 1. Add Items to Cart

```php
use Darkraul79\Cartify\Facades\Cart;

// Add product to cart
Cart::add(
    id: 1,
    name: 'Awesome Product',
    quantity: 2,
    price: 29.99,
    options: ['color' => 'blue', 'size' => 'M']
);

// Get cart totals
$subtotal = Cart::subtotal();
$tax = Cart::tax(0.21);
$total = Cart::total(0.21);

// Get cart content
$items = Cart::content();
```

### 2. Process Payment

```php
use Darkraul79\Payflow\Facades\Gateway;

// Calculate order total from cart
$total = Cart::total(config('simple-cart-payflow.cart.tax_rate'));

// Create payment
$payment = Gateway::withRedsys()->createPayment(
    amount: $total,
    orderId: 'ORDER-' . time(),
    options: [
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
        'url_notification' => route('payment.callback'),
    ]
);

// Redirect user to payment gateway
return redirect($payment['url']);
```

### 3. Handle Payment Callback

```php
use Darkraul79\Payflow\Facades\Gateway;
use Darkraul79\Cartify\Facades\Cart;

public function handleCallback(Request $request)
{
    $result = Gateway::withRedsys()->processCallback($request->all());
    
    if (Gateway::withRedsys()->isSuccessful($request->all())) {
        // Payment successful
        
        // Auto-clear cart if configured
        if (config('simple-cart-payflow.integration.auto_clear_cart_on_success')) {
            Cart::clear();
        }
        
        return redirect()->route('payment.success');
    }
    
    return redirect()->route('payment.error');
}
```

---

## Complete Example: Checkout Flow

```php
use Darkraul79\Cartify\Facades\Cart;
use Darkraul79\Payflow\Facades\Gateway;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout()
    {
        // Ensure cart is not empty
        if (Cart::isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }
        
        // Store cart for authenticated user
        if (Auth::check()) {
            Cart::store(Auth::id());
        }
        
        // Calculate totals
        $taxRate = config('simple-cart-payflow.cart.tax_rate');
        $total = Cart::total($taxRate);
        
        // Create order ID
        $orderId = 'ORDER-' . Auth::id() . '-' . time();
        
        // Create payment
        $payment = Gateway::withRedsys()->createPayment(
            amount: $total,
            orderId: $orderId,
            options: [
                'url_ok' => route('payment.success'),
                'url_ko' => route('payment.error'),
                'url_notification' => route('payment.callback'),
                'merchantData' => json_encode([
                    'user_id' => Auth::id(),
                    'cart_items' => Cart::content()->toArray(),
                ]),
            ]
        );
        
        // Redirect to payment gateway
        return redirect($payment['url']);
    }
    
    public function success()
    {
        return view('checkout.success');
    }
    
    public function error()
    {
        return view('checkout.error');
    }
    
    public function callback(Request $request)
    {
        // Process callback from payment gateway
        $result = Gateway::withRedsys()->processCallback($request->all());
        
        if (Gateway::withRedsys()->isSuccessful($request->all())) {
            // Payment successful - clear cart
            Cart::clear();
            
            // Store order, send emails, etc.
            // ...
            
            return response()->json(['status' => 'ok']);
        }
        
        return response()->json(['status' => 'error'], 400);
    }
}
```

---

## Documentation

For detailed documentation on each package:

- **Cartify Documentation**: [github.com/darkraul79/cartify](https://github.com/darkraul79/cartify)
- **Payflow Documentation**: [github.com/darkraul79/payflow](https://github.com/darkraul79/payflow)

---

## Testing

Run tests for the meta-package:

```bash
composer test
```

Run code formatting:

```bash
composer format
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## Security

If you discover any security-related issues, please email darkraul79@gmail.com instead of using the issue tracker.

---

## Credits

- [Raul Sebastian](https://github.com/darkraul79)
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

## Support

If you find this package useful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 📖 Improving documentation

---

## Roadmap

- [ ] Add Stripe gateway support
- [ ] Add PayPal gateway support
- [ ] Add order management integration
- [ ] Add email notifications for successful payments
- [ ] Add webhook handlers for delayed payments
- [ ] Add support for discount codes
- [ ] Add support for gift cards

