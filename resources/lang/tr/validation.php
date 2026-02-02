<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute alanı kabul edilmelidir.',
    'accepted_if' => ':other alanı :value olduğunda :attribute alanı kabul edilmelidir.',
    'active_url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'after' => ':attribute alanı :date tarihinden sonra bir tarih olmalıdır.',
    'after_or_equal' => ':attribute alanı :date tarihinden sonra veya aynı tarih olmalıdır.',
    'alpha' => ':attribute alanı yalnızca harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute alanı yalnızca harf, rakam, tire ve alt çizgi içerebilir.',
    'alpha_num' => ':attribute alanı yalnızca harf ve rakamlardan oluşmalıdır.',
    'any_of' => ':attribute alanı geçersiz.',
    'array' => ':attribute alanı bir dizi olmalıdır.',
    'ascii' => ':attribute alanı yalnızca tek baytlık alfanümerik karakterler ve semboller içermelidir.',
    'before' => ':attribute alanı :date tarihinden önce bir tarih olmalıdır.',
    'before_or_equal' => ':attribute alanı :date tarihinden önce veya aynı tarih olmalıdır.',
    'between' => [
        'array' => ':attribute alanı :min ile :max arasında öğe içermelidir.',
        'file' => ':attribute alanı :min ile :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute alanı :min ile :max arasında olmalıdır.',
        'string' => ':attribute alanı :min ile :max karakter arasında olmalıdır.',
    ],
    'boolean' => ':attribute alanı true veya false olmalıdır.',
    'can' => ':attribute alanı yetkisiz bir değer içeriyor.',
    'confirmed' => ':attribute doğrulaması eşleşmiyor.',
    'contains' => ':attribute alanında gerekli bir değer eksik.',
    'current_password' => 'Şifre hatalı.',
    'date' => ':attribute alanı geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute alanı :date tarihine eşit olmalıdır.',
    'date_format' => ':attribute alanı :format formatına uymalıdır.',
    'decimal' => ':attribute alanı :decimal ondalık basamağa sahip olmalıdır.',
    'declined' => ':attribute alanı reddedilmelidir.',
    'declined_if' => ':other alanı :value olduğunda :attribute alanı reddedilmelidir.',
    'different' => ':attribute alanı ile :other alanı farklı olmalıdır.',
    'digits' => ':attribute alanı :digits basamaklı olmalıdır.',
    'digits_between' => ':attribute alanı :min ile :max basamak arasında olmalıdır.',
    'dimensions' => ':attribute alanının görsel boyutları geçersiz.',
    'distinct' => ':attribute alanında yinelenen bir değer var.',
    'doesnt_contain' => ':attribute alanı şu değerlerden hiçbirini içermemelidir: :values.',
    'doesnt_end_with' => ':attribute alanı şu değerlerden biriyle bitmemelidir: :values.',
    'doesnt_start_with' => ':attribute alanı şu değerlerden biriyle başlamamalıdır: :values.',
    'email' => ':attribute alanı geçerli bir e-posta adresi olmalıdır.',
    'encoding' => ':attribute alanı :encoding formatında kodlanmış olmalıdır.',
    'ends_with' => ':attribute alanı şu değerlerden biriyle bitmelidir: :values.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'extensions' => ':attribute alanı şu uzantılardan birine sahip olmalıdır: :values.',
    'file' => ':attribute alanı bir dosya olmalıdır.',
    'filled' => ':attribute alanının bir değeri olmalıdır.',
    'gt' => [
        'array' => ':attribute alanı :value adetten fazla öğe içermelidir.',
        'file' => ':attribute alanı :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden büyük olmalıdır.',
        'string' => ':attribute alanı :value karakterden uzun olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute alanı en az :value öğe içermelidir.',
        'file' => ':attribute alanı :value kilobayta eşit veya daha büyük olmalıdır.',
        'numeric' => ':attribute alanı :value değerine eşit veya büyük olmalıdır.',
        'string' => ':attribute alanı :value karaktere eşit veya uzun olmalıdır.',
    ],
    'hex_color' => ':attribute alanı geçerli bir hexadecimal renk olmalıdır.',
    'image' => ':attribute alanı bir görsel olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'in_array' => ':attribute alanı :other içinde mevcut olmalıdır.',
    'in_array_keys' => ':attribute alanı şu anahtarlardan en az birini içermelidir: :values.',
    'integer' => ':attribute alanı bir tam sayı olmalıdır.',
    'ip' => ':attribute alanı geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute alanı geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute alanı geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute alanı geçerli bir JSON dizgesi olmalıdır.',
    'list' => ':attribute alanı bir liste olmalıdır.',
    'lowercase' => ':attribute alanı küçük harf olmalıdır.',
    'lt' => [
        'array' => ':attribute alanı :value adetten az öğe içermelidir.',
        'file' => ':attribute alanı :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden küçük olmalıdır.',
        'string' => ':attribute alanı :value karakterden kısa olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute alanı :value adetten fazla öğe içermemelidir.',
        'file' => ':attribute alanı :value kilobayta eşit veya küçük olmalıdır.',
        'numeric' => ':attribute alanı :value değerine eşit veya küçük olmalıdır.',
        'string' => ':attribute alanı :value karaktere eşit veya kısa olmalıdır.',
    ],
    'mac_address' => ':attribute alanı geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute alanı en fazla :max öğe içerebilir.',
        'file' => ':attribute alanı :max kilobayttan büyük olmamalıdır.',
        'numeric' => ':attribute alanı :max değerinden büyük olmamalıdır.',
        'string' => ':attribute alanı :max karakterden uzun olmamalıdır.',
    ],
    'max_digits' => ':attribute alanı en fazla :max basamaklı olmalıdır.',
    'mimes' => ':attribute alanı şu dosya türlerinden biri olmalıdır: :values.',
    'mimetypes' => ':attribute alanı şu dosya türlerinden biri olmalıdır: :values.',
    'min' => [
        'array' => ':attribute alanı en az :min öğe içermelidir.',
        'file' => ':attribute alanı en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute alanı en az :min olmalıdır.',
        'string' => ':attribute alanı en az :min karakter olmalıdır.',
    ],
    'min_digits' => ':attribute alanı en az :min basamaklı olmalıdır.',
    'missing' => ':attribute alanı bulunmamalıdır.',
    'missing_if' => ':other alanı :value olduğunda :attribute alanı bulunmamalıdır.',
    'missing_unless' => ':other alanı :value değilse :attribute alanı bulunmamalıdır.',
    'missing_with' => ':values mevcut olduğunda :attribute alanı bulunmamalıdır.',
    'missing_with_all' => ':values mevcut olduğunda :attribute alanı bulunmamalıdır.',
    'multiple_of' => ':attribute alanı :value değerinin katı olmalıdır.',
    'not_in' => 'Seçilen :attribute geçersiz.',
    'not_regex' => ':attribute alanı formatı geçersiz.',
    'numeric' => ':attribute alanı bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute alanı en az bir harf içermelidir.',
        'mixed' => ':attribute alanı en az bir büyük ve bir küçük harf içermelidir.',
        'numbers' => ':attribute alanı en az bir rakam içermelidir.',
        'symbols' => ':attribute alanı en az bir sembol içermelidir.',
        'uncompromised' => 'Girilen :attribute bir veri ihlalinde yer almış. Lütfen farklı bir :attribute seçin.',
    ],
    'present' => ':attribute alanı mevcut olmalıdır.',
    'present_if' => ':other alanı :value olduğunda :attribute alanı mevcut olmalıdır.',
    'present_unless' => ':other alanı :value değilse :attribute alanı mevcut olmalıdır.',
    'present_with' => ':values mevcut olduğunda :attribute alanı mevcut olmalıdır.',
    'present_with_all' => ':values mevcut olduğunda :attribute alanı mevcut olmalıdır.',
    'prohibited' => ':attribute alanı yasaklıdır.',
    'prohibited_if' => ':other alanı :value olduğunda :attribute alanı yasaklıdır.',
    'prohibited_if_accepted' => ':other alanı kabul edildiğinde :attribute alanı yasaklıdır.',
    'prohibited_if_declined' => ':other alanı reddedildiğinde :attribute alanı yasaklıdır.',
    'prohibited_unless' => ':other alanı :values içinde değilse :attribute alanı yasaklıdır.',
    'prohibits' => ':attribute alanı, :other alanının mevcut olmasını engeller.',
    'regex' => ':attribute alanı formatı geçersiz.',
    'required' => ':attribute alanı zorunludur.',
    'required_array_keys' => ':attribute alanı şu anahtarları içermelidir: :values.',
    'required_if' => ':other alanı :value olduğunda :attribute alanı zorunludur.',
    'required_if_accepted' => ':other alanı kabul edildiğinde :attribute alanı zorunludur.',
    'required_if_declined' => ':other alanı reddedildiğinde :attribute alanı zorunludur.',
    'required_unless' => ':other alanı :values içinde değilse :attribute alanı zorunludur.',
    'required_with' => ':values mevcut olduğunda :attribute alanı zorunludur.',
    'required_with_all' => ':values mevcut olduğunda :attribute alanı zorunludur.',
    'required_without' => ':values mevcut değilken :attribute alanı zorunludur.',
    'required_without_all' => ':values değerlerinin hiçbiri mevcut değilken :attribute alanı zorunludur.',
    'same' => ':attribute alanı :other ile aynı olmalıdır.',
    'size' => [
        'array' => ':attribute alanı :size öğe içermelidir.',
        'file' => ':attribute alanı :size kilobayt olmalıdır.',
        'numeric' => ':attribute alanı :size olmalıdır.',
        'string' => ':attribute alanı :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute alanı şu değerlerden biriyle başlamalıdır: :values.',
    'string' => ':attribute alanı bir metin olmalıdır.',
    'timezone' => ':attribute alanı geçerli bir zaman dilimi olmalıdır.',
    'unique' => ':attribute daha önce alınmış.',
    'uploaded' => ':attribute yüklenemedi.',
    'uppercase' => ':attribute alanı büyük harf olmalıdır.',
    'url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'ulid' => ':attribute alanı geçerli bir ULID olmalıdır.',
    'uuid' => ':attribute alanı geçerli bir UUID olmalıdır.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'özel-mesaj',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];
