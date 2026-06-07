<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\AiChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Models\Produk;
use App\Models\Order;


class AiCustomerServiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🤖 AI CUSTOMER SERVICE
    |--------------------------------------------------------------------------
    */
    public function chat(Request $request)
    {
        $request->validate([

            'message' => 'required|string|max:2000'

        ]);

        try {

            $message = trim($request->message);

            /*
            |--------------------------------------------------------------------------
            | 👤 USER
            |--------------------------------------------------------------------------
            */
            $user = auth('api')->user();

            /*
            |--------------------------------------------------------------------------
            | 🧠 DETECT INTENT
            |--------------------------------------------------------------------------
            */
            $intent = $this->detectIntent($message);

            /*
            |--------------------------------------------------------------------------
            | 🧠 CHAT HISTORY
            |--------------------------------------------------------------------------
            */
            $chatHistory = $this->getChatHistory(
                $user?->id
            );


            /*
            |--------------------------------------------------------------------------
            | 📦 BUILD CONTEXT
            |--------------------------------------------------------------------------
            */
            $context = $this->buildContext(
                $intent,
                $user
            );

            /*
            |--------------------------------------------------------------------------
            | 🧠 SYSTEM PROMPT
            |--------------------------------------------------------------------------
            */
            $prompt = $this->buildPrompt(
                $message,
                $intent,
                $context,
                $chatHistory
            );

            /*
            |--------------------------------------------------------------------------
            | 💾 SAVE USER CHAT
            |--------------------------------------------------------------------------
            */
            $this->saveChat(

                $user?->id,

                'user',

                $message
            );

            /*
            |--------------------------------------------------------------------------
            | 🚀 GEMINI API
            |--------------------------------------------------------------------------
            */
            $reply = $this->callGemini($prompt);

            /*
            |--------------------------------------------------------------------------
            | 💾 SAVE AI REPLY
            |--------------------------------------------------------------------------
            */
            $this->saveChat(

                $user?->id,

                'assistant',

                $reply
            );

            return response()->json([

                'success' => true,

                'message' => 'AI response berhasil',

                'data' => [

                    'intent' => $intent,

                    'reply' => trim($reply)
                ]
            ]);

        } catch (\Throwable $e) {

            Log::error('AI Customer Service Error', [

                'message' => $e->getMessage(),

                'line' => $e->getLine(),

                'file' => $e->getFile()
            ]);

            
            return response()->json([

                'success' => false,

                'message' => 'AI gagal merespon',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 DETECT INTENT
    |--------------------------------------------------------------------------
    */
    private function detectIntent(string $message): string
    {
        $message = strtolower($message);

        /*
        |--------------------------------------------------------------------------
        | 📦 ORDER
        |--------------------------------------------------------------------------
        */
        if (

            str_contains($message, 'pesanan') ||
            str_contains($message, 'order') ||
            str_contains($message, 'resi') ||
            str_contains($message, 'tracking')

        ) {
            return 'ORDER_TRACKING';
        }

        /*
        |--------------------------------------------------------------------------
        | 💳 PAYMENT
        |--------------------------------------------------------------------------
        */
        if (

            str_contains($message, 'bayar') ||
            str_contains($message, 'payment') ||
            str_contains($message, 'midtrans') ||
            str_contains($message, 'transfer')

        ) {
            return 'PAYMENT';
        }

        /*
        |--------------------------------------------------------------------------
        | 🛒 CHECKOUT
        |--------------------------------------------------------------------------
        */
        if (

            str_contains($message, 'checkout') ||
            str_contains($message, 'ongkir') ||
            str_contains($message, 'alamat') ||
            str_contains($message, 'keranjang')

        ) {
            return 'CHECKOUT';
        }

        /*
        |--------------------------------------------------------------------------
        | 🍈 PRODUCT
        |--------------------------------------------------------------------------
        */
        if (

            str_contains($message, 'melon') ||
            str_contains($message, 'produk') ||
            str_contains($message, 'manis') ||
            str_contains($message, 'stok') ||
            str_contains($message, 'variasi')

        ) {
            return 'PRODUCT';
        }

        /*
        |--------------------------------------------------------------------------
        | 🔐 LOGIN
        |--------------------------------------------------------------------------
        */
        if (

            str_contains($message, 'login') ||
            str_contains($message, 'google') ||
            str_contains($message, 'password') ||
            str_contains($message, 'akun')

        ) {
            return 'AUTH';
        }

        return 'GENERAL';
    }

    /*
    |--------------------------------------------------------------------------
    | 📦 BUILD CONTEXT
    |--------------------------------------------------------------------------
    */
    private function buildContext(
        string $intent,
        $user
    ): string {

        switch ($intent) {

            /*
            |--------------------------------------------------------------------------
            | 🍈 PRODUCT
            |--------------------------------------------------------------------------
            */
            case 'PRODUCT':

                return $this->buildProductContext();

            /*
            |--------------------------------------------------------------------------
            | 📦 ORDER
            |--------------------------------------------------------------------------
            */
            case 'ORDER_TRACKING':

                return $this->buildOrderContext($user);

            /*
            |--------------------------------------------------------------------------
            | 💳 PAYMENT
            |--------------------------------------------------------------------------
            */
            case 'PAYMENT':

                return '
                Pembayaran menggunakan Midtrans.
                User dapat menggunakan:
                - QRIS
                - Bank Transfer
                - E-Wallet
                - Virtual Account

                Jika pembayaran pending:
                - cek aplikasi pembayaran
                - cek status order
                - lakukan refresh halaman
                ';

            /*
            |--------------------------------------------------------------------------
            | 🛒 CHECKOUT
            |--------------------------------------------------------------------------
            */
            case 'CHECKOUT':

                return '
                Sebelum checkout user wajib:
                - login akun
                - melengkapi profil
                - mengisi alamat lengkap
                - mengisi nomor HP
                - memilih ongkir

                Website mendukung:
                - cart
                - ongkir otomatis
                - checkout Midtrans
                ';

            /*
            |--------------------------------------------------------------------------
            | 🔐 AUTH
            |--------------------------------------------------------------------------
            */
            case 'AUTH':

                return '
                User dapat login menggunakan:
                - nomor handphone
                - akun Google

                User juga dapat:
                - mengganti password
                - menghubungkan Google
                - mengubah nomor HP
                ';

            default:

                return '
                Melonku adalah platform penjualan melon premium langsung dari greenhouse.
                ';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🍈 PRODUCT CONTEXT
    |--------------------------------------------------------------------------
    */
private function buildProductContext(): string
{
    $produkList = Produk::with([
        'variasi',
        'greenhouse'
    ])
    ->where('stok', '>', 0)
    ->latest()
    ->take(10)
    ->get();

    if ($produkList->isEmpty()) {

        return '
        Saat ini produk melon sedang kosong.
        ';
    }

    $text = "
    Daftar produk Melonku yang tersedia:
    ";

    foreach ($produkList as $produk) {

        $text .= "

        Nama Produk:
        {$produk->nama}

        Harga:
        Rp " . number_format($produk->harga, 0, ',', '.') . "

        Stok:
        {$produk->stok}

        Deskripsi:
        " . ($produk->deskripsi ?? 'Tidak ada deskripsi') . "

        Greenhouse:
        " . ($produk->greenhouse->nama ?? '-') . "

        ";

        /*
        |--------------------------------------------------------------------------
        | 🍈 VARIASI
        |--------------------------------------------------------------------------
        */
        foreach ($produk->variasi as $variasi) {

            $text .= "

            Variasi:
            {$variasi->nama}

            Harga Variasi:
            Rp " . number_format($variasi->harga, 0, ',', '.') . "

            Stok Variasi:
            {$variasi->stok}

            ";
        }

        $text .= "\n-----------------------------------\n";
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 AI INSTRUCTION
    |--------------------------------------------------------------------------
    */
    $text .= "

    Instruksi:
    - rekomendasikan produk yang stoknya tersedia
    - bantu user memilih melon
    - jika user mencari melon manis, rekomendasikan produk terbaik
    - jawab singkat dan natural
    ";

    return $text;
}
    /*
    |--------------------------------------------------------------------------
    | 📦 ORDER CONTEXT
    |--------------------------------------------------------------------------
    */
private function buildOrderContext($user): string
{
    /*
    |--------------------------------------------------------------------------
    | ❌ USER BELUM LOGIN
    |--------------------------------------------------------------------------
    */
    if (!$user) {

        return '
        User belum login.

        Untuk melihat status pesanan:
        - login terlebih dahulu
        - buka menu pesanan saya
        ';
    }

    /*
    |--------------------------------------------------------------------------
    | 📦 GET ORDERS
    |--------------------------------------------------------------------------
    */
    $orders = Order::with([
        'items.produk',
        'alamat'
    ])
    ->where('user_id', $user->id)
    ->latest()
    ->take(5)
    ->get();

    /*
    |--------------------------------------------------------------------------
    | ❌ NO ORDER
    |--------------------------------------------------------------------------
    */
    if ($orders->isEmpty()) {

        return '
        User belum memiliki pesanan.
        ';
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 BUILD ORDER CONTEXT
    |--------------------------------------------------------------------------
    */
    $text = "
    Data pesanan user:
    ";

    foreach ($orders as $order) {

        $text .= "

        ===================================

        ORDER ID:
        {$order->id}

        STATUS ORDER:
        {$order->status}

        STATUS PEMBAYARAN:
        {$order->payment_status}

        TOTAL BELANJA:
        Rp " . number_format($order->total_price, 0, ',', '.') . "

        ALAMAT:
        " . ($order->alamat->alamat_lengkap ?? '-') . "

        ";

        /*
        |--------------------------------------------------------------------------
        | 🛒 ITEMS
        |--------------------------------------------------------------------------
        */
        foreach ($order->items as $item) {

            $text .= "

            Produk:
            " . ($item->produk->nama ?? '-') . "

            Qty:
            {$item->qty}

            Harga:
            Rp " . number_format($item->price, 0, ',', '.') . "

            ";
        }

        $text .= "

        ===================================

        ";
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 AI INSTRUCTION
    |--------------------------------------------------------------------------
    */
    $text .= "

    Instruksi:
    - bantu user mengecek pesanan
    - jawab dengan ramah
    - jika pembayaran pending suruh selesaikan pembayaran
    - jika pesanan diproses beri estimasi menunggu
    - jika pesanan dikirim suruh cek resi
    ";

    return $text;
}

    /*
    |--------------------------------------------------------------------------
    | 🧠 BUILD PROMPT
    |--------------------------------------------------------------------------
    */
    private function buildPrompt(
        string $message,
        string $intent,
        string $context,
        string $chatHistory
    ): string{

        return "
        Kamu adalah AI Customer Service resmi Melonku.

        Karakter:
        - ramah
        - santai
        - membantu
        - singkat jelas
        - tidak kaku

        Rules:
        - fokus hanya tentang Melonku
        - jangan jawab di luar konteks
        - jangan gunakan markdown
        - gunakan bahasa Indonesia
        - jangan terlalu panjang
        - bantu user step by step

        Jika user marah:
        - tetap sopan
        - minta maaf
        - bantu selesaikan masalah

        Intent user:
        {$intent}

        Context:
        {$context}

        Pertanyaan user:
        {$message}

        Chat history:
        {$chatHistory}
        ";
    }

/*
|--------------------------------------------------------------------------
| 🚀 CALL AI (OPENROUTER)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| 🚀 CALL AI (OPENROUTER MULTI FALLBACK)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| 🚀 CALL GROQ AI
|--------------------------------------------------------------------------
*/
private function callGemini(string $prompt): string
{
    try {

        $response = Http::timeout(60)

            ->withHeaders([

                'Authorization' => 'Bearer ' . config('services.groq.api_key'),

                'Content-Type' => 'application/json',

            ])

            ->post(

                'https://api.groq.com/openai/v1/chat/completions',

                [

                    'model' => config('services.groq.model'),

                    'messages' => [

                        [
                            'role' => 'system',
                            'content' => 'Kamu adalah AI Customer Service Melonku yang ramah, santai, membantu, dan fokus menjawab seputar website Melonku.'
                        ],

                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]

                    ],

                    'temperature' => 0.7,

                    'max_tokens' => 300,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | ❌ ERROR RESPONSE
        |--------------------------------------------------------------------------
        */
        if (!$response->successful()) {

            Log::error('GROQ ERROR', [

                'status' => $response->status(),

                'body' => $response->json(),
            ]);

            return 'Maaf, AI sedang sibuk. Silakan coba beberapa saat lagi.';
        }

        /*
        |--------------------------------------------------------------------------
        | ✅ SUCCESS RESPONSE
        |--------------------------------------------------------------------------
        */
        $result = $response->json();

        Log::info('GROQ RESULT', $result);

        return data_get(

            $result,

            'choices.0.message.content',

            'Maaf, AI tidak dapat menjawab saat ini.'
        );

    } catch (\Throwable $e) {

        Log::error('GROQ EXCEPTION', [

            'message' => $e->getMessage()
        ]);

        return 'Maaf, terjadi gangguan pada AI.';
    }
}
    /*
    |--------------------------------------------------------------------------
    | 💾 SAVE CHAT
    |--------------------------------------------------------------------------
    */
    private function saveChat(
        ?int $userId,
        string $role,
        string $message
    ): void {

        AiChatHistory::create([

            'user_id' => $userId,

            'role' => $role,

            'message' => $message,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 GET CHAT HISTORY
    |--------------------------------------------------------------------------
    */
    private function getChatHistory(
        ?int $userId
    ): string {

        if (!$userId) {

            return '';
        }

        $histories = AiChatHistory::where(
                'user_id',
                $userId
            )
            ->latest()
            ->take(6)
            ->get()
            ->reverse();

        if ($histories->isEmpty()) {

            return '';
        }

        $text = '';

        foreach ($histories as $chat) {

            $text .= strtoupper($chat->role) .
                ': ' .
                $chat->message .
                "\n";
        }

        return $text;
    }



}