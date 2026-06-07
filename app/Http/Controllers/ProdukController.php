<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📦 LIST PRODUK
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $produk = Produk::query()

            ->with($this->relations())

->select([
    'id',
    'nama',
    'slug',
    'harga',
    'stok',
    'berat',
    'gambar'
])

            ->when($request->search, function ($q) use ($request) {

                $q->where(
                    'nama',
                    'like',
                    '%' . $request->search . '%'
                );
            })

            ->latest()

            ->paginate(10);

        return response()->json([

            'data' => $produk->getCollection()
                ->map(fn($item) => $this->formatProduct($item)),

            'meta' => $this->metaPagination($produk)
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 DETAIL PRODUK
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $produk = Produk::with([

            ...$this->relations(),

            'detail',
            'timeline',
            'seller'

        ])->findOrFail($id);

        return response()->json([

            'data' => [

                'id' => $produk->id,

                'nama' => $produk->nama,

                'slug' => $produk->slug,

                ...$this->pricing($produk),

                'stok' => (int) $produk->stok,
                
                'berat' => (int) $produk->berat,

                'gambar' => $this->getImageUrl($produk),

                'images' => $produk->sources
                    ->map(fn($img) => $this->fullUrl($img->path)),

                'promo' => $this->formatPromo($produk),
                
                'varian' => $produk->varian->map(function ($v) {
                
                    return [
                
                        'id' => $v->id,
                
                        'berat' => (int) $v->berat,
                
                        'harga' => (int) $v->harga,
                
                        'stok' => (int) $v->stok,
                    ];
                }),


                'detail' => $this->formatDetail($produk),

                'timeline' => $produk->timeline,

                'seller' => [
                    'id' => $produk->seller?->id,
                    'name' => $produk->seller?->name
                ]
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELATIONS
    |--------------------------------------------------------------------------
    */
    private function relations(): array
    {
        return [
    
            'sources' => fn($q)
                => $q->where('is_featured', 1),
    
            'promo' => fn($q)
                => $q->active(),
    
            'varian'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 FORMAT PRODUK
    |--------------------------------------------------------------------------
    */
    private function formatProduct($item): array
    {
return [

    'id' => $item->id,

    'nama' => $item->nama,

    'slug' => $item->slug,

    ...$this->pricing($item),

    'stok' => (int) $item->stok,

    'berat' => (int) $item->berat,

    'gambar' => $this->getImageUrl($item),

    'promo' => $this->formatPromo($item),
    
    'varian' => $item->varian->map(function ($v) {
    
        return [
    
            'id' => $v->id,
    
            'berat' => (int) $v->berat,
    
            'harga' => (int) $v->harga,
    
            'stok' => (int) $v->stok,
        ];
    }),
];
    }

    /*
    |--------------------------------------------------------------------------
    | 🎁 FORMAT PROMO
    |--------------------------------------------------------------------------
    */
    private function formatPromo($produk)
    {
        return $produk->promo->map(function ($promo) {

            return [

                'id' => $promo->id,

                'nama' => $promo->nama,

                'tipe' => $promo->tipe,

                'diskon' => (float) $promo->diskon,

                'minimal_belanja' =>
                    (float) $promo->minimal_belanja,

                'maksimal_diskon' =>
                    $promo->maksimal_diskon
                        ? (float) $promo->maksimal_diskon
                        : null,

                'banner' => $promo->banner
                    ? asset('storage/' . $promo->banner)
                    : null,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 💰 HITUNG HARGA FINAL
    |--------------------------------------------------------------------------
    */
    private function pricing($produk): array
    {
        $hargaAsli = (int) $produk->harga;

        $promo = $produk->promo
            ->sortByDesc('is_flashsale')
            ->first();
        /*
        |--------------------------------------------------------------------------
        | TANPA PROMO
        |--------------------------------------------------------------------------
        */
        if (!$promo) {

            return [

                'harga_asli' => $hargaAsli,

                'harga_final' => $hargaAsli,

                'diskon_nominal' => 0,

                'promo_label' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | HITUNG DISKON
        |--------------------------------------------------------------------------
        */
        $diskonNominal = 0;

        if ($promo->tipe === 'percent') {

            $diskonNominal =
                ($hargaAsli * $promo->diskon) / 100;

            /*
            |--------------------------------------------------------------------------
            | MAXIMAL DISKON
            |--------------------------------------------------------------------------
            */
            if (
                $promo->maksimal_diskon &&
                $diskonNominal > $promo->maksimal_diskon
            ) {

                $diskonNominal =
                    $promo->maksimal_diskon;
            }

        } else {

            $diskonNominal =
                $promo->diskon;
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL PRICE
        |--------------------------------------------------------------------------
        */
        $hargaFinal =
            $hargaAsli - $diskonNominal;

        /*
        |--------------------------------------------------------------------------
        | ANTI MINUS
        |--------------------------------------------------------------------------
        */
        if ($hargaFinal < 0) {

            $hargaFinal = 0;
        }

        return [

            'harga_asli' => $hargaAsli,

            'harga_final' => (int) $hargaFinal,

            'diskon_nominal' => (int) $diskonNominal,

            'promo_label' => $promo->is_flashsale
                ? 'FLASH SALE ' . (int) $promo->diskon . '%'
                : $this->promoLabel($promo),

            'is_flashsale' => (bool) $promo->is_flashsale,

            'flashsale_stock' => $promo->flashsale_stock,

            'flashsale_limit' => $promo->flashsale_limit,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🏷 PROMO LABEL
    |--------------------------------------------------------------------------
    */
    private function promoLabel($promo): string
    {
        if ($promo->tipe === 'percent') {

            return 'DISKON ' .
                (int) $promo->diskon . '%';
        }

        return 'HEMAT Rp ' .
            number_format(
                $promo->diskon,
                0,
                ',',
                '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | 📄 FORMAT DETAIL
    |--------------------------------------------------------------------------
    */
    private function formatDetail($produk): array
    {
        return [

            'short_description' =>
                $produk->detail?->short_description,

            'long_description' =>
                $produk->detail?->long_description,

            'characteristics' => [

                'sweetness' => [
                    'value' => $produk->detail?->sweetness,
                    'label' => $produk->detail?->sweetness_label,
                ],

                'juiciness' => [
                    'value' => $produk->detail?->juiciness,
                    'label' => $produk->detail?->juiciness_label,
                ],

                'texture' => [
                    'value' => $produk->detail?->texture,
                    'label' => $produk->detail?->texture_label,
                ],
            ],

            'nutrition' => [

                'serving_size' =>
                    $produk->detail?->serving_size,

                'calories' =>
                    $produk->detail?->calories,

                'vitamin_c' =>
                    $produk->detail?->vitamin_c,

                'potassium' =>
                    $produk->detail?->potassium,

                'fiber' =>
                    $produk->detail?->fiber,
            ],

            'product_info' => [

                'storage_instruction' =>
                    $produk->detail?->storage_instruction,

                'origin_farm' =>
                    $produk->detail?->origin_farm,

                'harvest_age' =>
                    $produk->detail?->harvest_age,
            ],

            'review_enabled' =>
                (bool) $produk->detail?->review_enabled,

            'rating' =>
                $produk->detail?->review_enabled
                    ? $produk->detail?->rating
                    : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🖼 HANDLE IMAGE URL
    |--------------------------------------------------------------------------
    */
    private function getImageUrl($produk)
    {
        $featured = optional(
            $produk->sources->first()
        )->path;

        return $this->fullUrl(
            $featured ?? $produk->gambar
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🔗 FULL URL
    |--------------------------------------------------------------------------
    */
    private function fullUrl($path)
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset(
            'storage/' . ltrim($path, '/')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 📄 META PAGINATION
    |--------------------------------------------------------------------------
    */
    private function metaPagination($data): array
    {
        return [

            'current_page' => $data->currentPage(),

            'last_page' => $data->lastPage(),

            'per_page' => $data->perPage(),

            'total' => $data->total(),
        ];
    }
}