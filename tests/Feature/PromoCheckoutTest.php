<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Greenhouse;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Promo;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ShippingCourier;
use App\Models\ShippingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;
    private $product;
    private $varian;
    private $promo;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Roles
        $adminRole = Role::create(['nama' => 'admin']);
        $customerRole = Role::create(['nama' => 'customer']);

        // 2. Create Users
        $this->admin = User::create([
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        UserRole::create(['user_id' => $this->admin->id, 'role_id' => $adminRole->id]);

        $this->user = User::create([
            'username' => 'customer_test',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        UserRole::create(['user_id' => $this->user->id, 'role_id' => $customerRole->id]);

        // Seed Province, Kabupaten, Kecamatan, and Village for foreign key constraints using DB facade to bypass Eloquent $guarded
        \Illuminate\Support\Facades\DB::table('provinsi')->insert([
            'id' => 11,
            'nama' => 'Aceh',
        ]);

        \Illuminate\Support\Facades\DB::table('kabupaten')->insert([
            'id' => 1101,
            'provinsi_id' => 11,
            'nama' => 'Kabupaten Aceh Barat',
        ]);

        \Illuminate\Support\Facades\DB::table('kecamatan')->insert([
            'id' => 1101010,
            'kabupaten_id' => 1101,
            'nama' => 'Kecamatan Teunom',
        ]);

        \Illuminate\Support\Facades\DB::table('villages')->insert([
            'id' => 1101010001,
            'districts_id' => 1101010,
            'nama' => 'Desa Test',
        ]);

        // Create Profile for checkout validation
        UserProfile::create([
            'user_id' => $this->user->id,
            'nama' => 'Customer Test',
            'alamat_detail' => 'Test Street 123',
            'provinsi_id' => '11',
            'kabupaten_id' => '1101',
            'kecamatan_id' => '1101010',
            'village_id' => '1101010001',
        ]);

        // 3. Create Greenhouse & Product
        $greenhouse = Greenhouse::create([
            'nama' => 'Greenhouse Test',
            'deskripsi' => 'Greenhouse test desc',
            'alamat' => 'Test Farm address',
        ]);

        $this->product = Produk::create([
            'nama' => 'Melon Premium',
            'slug' => 'melon-premium',
            'harga' => 50000,
            'stok' => 10,
            'berat' => 1000,
            'greenhouse_id' => $greenhouse->id,
            'user_id' => $this->admin->id,
        ]);

        $this->varian = ProdukVarian::create([
            'produk_id' => $this->product->id,
            'nama' => 'Varian A',
            'berat' => 1000,
            'harga' => 50000,
            'stok' => 10,
        ]);

        // 4. Create Promo Voucher
        $this->promo = Promo::create([
            'nama' => 'Diskon Awal',
            'slug' => 'diskon-awal',
            'kode_promo' => 'PROMO10',
            'tipe' => 'percent',
            'diskon' => 10, // 10%
            'minimal_belanja' => 20000,
            'status' => true,
            'tanggal_mulai' => now()->subDay(),
            'tanggal_selesai' => now()->addDay(),
        ]);
    }

    /**
     * Test /promo/check endpoint.
     */
    public function test_promo_check_endpoint(): void
    {
        // 1. Success check
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/promo/check', [
                'kode_promo' => 'PROMO10'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'kode_promo' => 'PROMO10',
                    'tipe' => 'percent',
                    'diskon' => 10
                ]
            ]);

        // 2. Not found / inactive check
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/promo/check', [
                'kode_promo' => 'INVALIDCODE'
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test checkout calculates and applies promo discount.
     */
    public function test_checkout_applies_promo_discount(): void
    {
        // Setup Cart with item
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'produk_varian_id' => $this->varian->id,
            'qty' => 2, // Subtotal: 2 * 50000 = 100000
        ]);

        $payload = [
            'receiver_name' => 'John Doe',
            'receiver_phone' => '0812345678',
            'receiver_address' => 'Customer Street Address [Koordinat: -7.25, 112.76]',
            'receiver_province' => 'Jawa Timur',
            'receiver_city' => 'Surabaya',
            'receiver_district' => 'Gubeng',
            'courier' => 'JNE',
            'shipping_service' => 'REG',
            'shipping_cost' => 15000,
            'shipping_estimation' => '2-3 Hari',
            'kode_promo' => 'PROMO10', // 10% of 100000 = 10000
        ];

        // Call checkout
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/checkout', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // Total price should be: subtotal (100000) + shipping (15000) - discount (10000) = 105000
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_price' => 105000,
            'voucher_code' => 'PROMO10',
            'voucher_discount' => 10000,
        ]);
    }

    /**
     * Test dynamic origin_city_id update and lookup.
     */
    public function test_ongkir_origin_city_id(): void
    {
        // 1. Update settings via Panel API as Admin
        $payload = [
            'origin_city_id' => '152',
            'origin_city_name' => 'Surabaya',
            'origin_province_name' => 'Jawa Timur',
            'warehouse_name' => 'Gudang Surabaya',
            'rajaongkir_api_key' => 'test_key',
            'is_rajaongkir_active' => true,
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/v1/panel/ongkir', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('shipping_settings', [
            'origin_city_id' => '152',
            'warehouse_name' => 'Gudang Surabaya',
        ]);
    }
}
