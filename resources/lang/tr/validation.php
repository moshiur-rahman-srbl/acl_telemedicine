<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'              => ':attribute kabul edilmelidir.',
    'active_url'            => ':attribute geçerli bir URL olmalıdır.',
    'after'                 => ':attribute, :date tarihinden daha eski bir tarih olmalıdır .',
    'after_or_equal'        => ':attribute, :date tarihine eşit ya da daha yeni bir tarih olmalıdır.',
    'alpha'                 => ':attribute sadece harflerden oluşmalıdır.',
    'alpha_dash'            => ':attribute sadece harfler, rakamlar ve tirelerden oluşmalıdır.',
    'alpha_num'             => ':attribute sadece harfler ve rakamlar içermelidir.',
    'array'                 => ':attribute dizi olmalıdır.',
    'before'                => ':attribute, :data tarihinden daha eski bir tarih olmalıdır.',
    'before_or_equal'       => ':attribute, :date tarihine eşit ya da daha eski bir tarih olmalıdır.',
    'between'              => [
        'numeric' => ':attribute :min - :max arasında olmalıdır.',
        'file'    => ':attribute :min kilobayt - :max kilobayt değerleri arasında olmalıdır.',
        'string'  => ':attribute en az :min, en çok :max karakterden oluşmalıdır.',
        'array'   => ':attribute :min - :max arasında nesneye sahip olmalıdır.'
    ],
    'boolean'              => ':attribute sadece doğru veya yanlış değerlerini alabilir.',
    'confirmed'            => ':attribute tekrarı ile eşleşmiyor.',
    'date'                 => ':attribute geçerli bir tarih değil.',
    'date_format'          => ':attribute tarihi :format formatına uymuyor.',
    'different'            => ':attribute ile :other birbirinden farklı olmalıdır.',
    'digits'               => ':attribute :digits rakam olmalıdır.',
    'digits_between'       => ':attribute :min ile :max arasında bir rakam olmalıdır.',
    'dimensions'           => ':attribute geçersiz görsel ölçülerine sahip.',
    'distinct'             => ':attribute alanı eşsiz bir değere sahip olmalıdır.',
    'email'                => ':attribute geçerli bir email formatında olmalıdır.',
    'exists'               => 'Seçilen :attribute geçersiz.',
    'file'                 => ':attribute bir dosya olmalıdır.',
    'filled'               => ':attribute alanının doldurulması zorunludur.',
    'image'                => ':attribute bir görsel olmalıdır.',
    'in'                   => 'Seçilen :attribute değeri geçersiz.',
    'in_array'             => 'Seçilen :attribute alanı :other alanında mevcut değil.',
    'integer'              => ':attribute bir tamsayı olmalıdır.',
    'ip'                   => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4'                 => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6'                 => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json'                 => ':attribute geçerli bir JSON yapısına sahip olmalıdır.',
    'max'                  => [
        'numeric' => ':attribute değeri :max değerinden küçük olmalıdır.',
        'file'    => ':attribute değeri :max kilobayt değerinden küçük olmalıdır.',
        'string'  => ':attribute değeri :max karakterden kısa olmalıdır.',
        'array'   => ':attribute değeri :max adedinden az nesneye sahip olmalıdır.'
    ],
    'mimes'                => ':attribute dosya tipi :values olmalıdır.',
    'mimetypes'            => ':attribute şu dosya tiplerinden birine sahip olmalıdır: :values.',
    'min'                  => [
        'numeric' => ':attribute değeri :min değerinden büyük olmalıdır.',
        'file'    => ':attribute değeri :min kilobayt değerinden büyük olmalıdır.',
        'string'  => ':attribute değeri :min karakterden uzun olmalıdır.',
        'array'   => ':attribute değeri :min adedinden çok nesneye sahip olmalıdır.'
    ],
    'not_in'               => 'Seçilen :attribute değeri geçersiz',
    'numeric'              => ':attribute bir sayı olmalıdır.',
    'present'              => ':attribute değeri mevcut olmalıdır.',
    'regex'                => ':attribute istenilen formatta değil.',
    'required'             => ':attribute alanı gereklidir.',
    'required_if'          => ':attribute alanı, :other alanı :value değerindeyken gereklidir.',
    'required_unless'      => ':attribute alanı, :other alanı :values değerinde değilken gereklidir.',
    'required_with'        => ':attribute alanı, :values değeri mevcutken gereklidir.',
    'required_with_all'    => ':attribute alanı, :values değerleri mevcutken gereklidir.',
    'required_without'     => ':attribute alanı, :values değeri mevcut değilken gereklidir.',
    'required_without_all' => ':attribute alanı, :values değerlerinden hiçbiri mevcut değilken gereklidir.',
    'same'                 => ':attribute ve :other alanları eşleşmelidir.',
    'size'                 => [
        'numeric' => ':attribute, tam olarak :size değerine eşit olmalıdır.',
        'file'    => ':attribute, tam olarak :size kilobayt olmalıdır.',
        'string'  => ':attribute, tam olarak :size karakter içermelidir.',
        'array'   => ':attribute, tam olarak :size nesne içermelidir.',
    ],
    'string'               => ':attribute geçerli bir yazı dizgisi olmalıdır.',
    'timezone'             => ':attribute geçerli bir zaman dilimi olmalıdır.',
    'unique'               => ':attribute zaten mevcut.',
    'uploaded'             => ':attribute yüklemesi başarısız oldu.',
    'url'                  => ':attribute geçerli bir formatta değil.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention 'attribute.rule' to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of 'email'. This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'new_password' => 'Yeni Şifre',
        'confirm_new_password' => 'Şifreyi tekrar girin',
    ],

];