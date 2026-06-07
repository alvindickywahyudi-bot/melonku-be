<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | 📦 ORDER STATUS
    |--------------------------------------------------------------------------
    */
const STATUS_PENDING   = 'pending';

const STATUS_PROCESSED = 'diproses';

const STATUS_PACKED    = 'dikemas';

const STATUS_SHIPPED   = 'dikirim';

const STATUS_COMPLETED = 'selesai';

const STATUS_CANCELLED = 'dibatalkan';

    /*
    |--------------------------------------------------------------------------
    | 💳 PAYMENT STATUS
    |--------------------------------------------------------------------------
    */
    const PAYMENT_PENDING = 'pending';

    const PAYMENT_PAID    = 'paid';

    const PAYMENT_FAILED  = 'failed';

    const PAYMENT_EXPIRED = 'expired';

    /*
    |--------------------------------------------------------------------------
    | 🧱 TABLE
    |--------------------------------------------------------------------------
    */
    protected $table = 'orders';

    /*
    |--------------------------------------------------------------------------
    | 🛡️ FILLABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */
        'user_id',

        /*
        |--------------------------------------------------------------------------
        | PRICE
        |--------------------------------------------------------------------------
        */
        'total_price',

        'voucher_code',

        'voucher_discount',

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */
        'status',

        'payment_status',

        /*
        |--------------------------------------------------------------------------
        | MIDTRANS
        |--------------------------------------------------------------------------
        */
        'midtrans_order_id',

        /*
        |--------------------------------------------------------------------------
        | SHIPPING
        |--------------------------------------------------------------------------
        */
        'courier',

        'shipping_service',

        'shipping_cost',

        'shipping_estimation',

        /*
        |--------------------------------------------------------------------------
        | RECEIVER
        |--------------------------------------------------------------------------
        */
        'receiver_name',

        'receiver_phone',

        'receiver_address',

        'receiver_province',

        'receiver_city',

        'receiver_district',

        /*
        |--------------------------------------------------------------------------
        | DELIVERY
        |--------------------------------------------------------------------------
        */
        'resi',

        /*
        |--------------------------------------------------------------------------
        | TIMESTAMP
        |--------------------------------------------------------------------------
        */
        'paid_at',

        'shipped_at',

        'completed_at',

        'cancelled_at',

        'expired_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔄 CASTS
    |--------------------------------------------------------------------------
    */
    protected $casts = [

        'total_price' => 'integer',

        'voucher_discount' => 'integer',

        'shipping_cost' => 'integer',

        'paid_at' => 'datetime',

        'shipped_at' => 'datetime',

        'completed_at' => 'datetime',

        'cancelled_at' => 'datetime',

        'expired_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ITEMS
    |--------------------------------------------------------------------------
    */
    public function items()
    {
        return $this->hasMany(
            OrderItem::class,
            'order_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 💡 HELPERS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | PAYMENT
    |--------------------------------------------------------------------------
    */
    public function isPaid()
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isPaymentPending()
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }

    public function isPaymentFailed()
    {
        return $this->payment_status === self::PAYMENT_FAILED;
    }

    public function isPaymentExpired()
    {
        return $this->payment_status === self::PAYMENT_EXPIRED;
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER STATUS
    |--------------------------------------------------------------------------
    */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessed()
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function isPacked()
    {
        return $this->status === self::STATUS_PACKED;
    }

    public function isShipped()
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /*
    |--------------------------------------------------------------------------
    | 🎨 BADGE COLOR
    |--------------------------------------------------------------------------
    */
    public function statusColor()
    {
        return match ($this->status) {

            self::STATUS_PENDING => 'yellow',

            self::STATUS_PROCESSED => 'blue',

            self::STATUS_PACKED => 'indigo',

            self::STATUS_SHIPPED => 'purple',

            self::STATUS_COMPLETED => 'green',

            self::STATUS_CANCELLED => 'red',

            default => 'gray',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | 🎨 PAYMENT BADGE
    |--------------------------------------------------------------------------
    */
    public function paymentColor()
    {
        return match ($this->payment_status) {

            self::PAYMENT_PENDING => 'yellow',

            self::PAYMENT_PAID => 'green',

            self::PAYMENT_FAILED => 'red',

            self::PAYMENT_EXPIRED => 'gray',

            default => 'gray',
        };
    }
}