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

    'accepted' => 'Ei :attribute grohon korte hobe.',
    'accepted_if' => 'Jokhon :other :value hoy, tokhon ei :attribute grohon korte hobe.',
    'active_url' => 'Ei :attribute ekta boidho URL noy.',
    'after' => 'Ei :attribute :date er porer ekta tarikh hote hobe.',
    'after_or_equal' => 'Ei :attribute :date er porer ba shoman ekta tarikh hote hobe.',
    'alpha' => 'Ei :attribute shudhu okkhor dhārōn korte parbe.',
    'alpha_dash' => 'Ei :attribute shudhu okkhor, shongkhya, dash ebong underscore dhārōn korte parbe.',
    'alpha_num' => 'Ei :attribute shudhu okkhor ebong shongkhya dhārōn korte parbe.',
    'array' => 'Ei :attribute ekta array hote hobe.',
    'ascii' => 'Ei :attribute shudhu single-byte alphanumeric okkhor ebong chinhô dhārōn korte parbe.',
    'before' => 'Ei :attribute :date er ager ekta tarikh hote hobe.',
    'before_or_equal' => 'Ei :attribute :date er ager ba shoman ekta tarikh hote hobe.',
    'between' => [
        'array' => 'Ei :attribute e :min theke :max ti item thakte hobe.',
        'file' => 'Ei :attribute :min theke :max kilobyte er moddhe hote hobe.',
        'numeric' => 'Ei :attribute :min theke :max er moddhe hote hobe.',
        'string' => 'Ei :attribute :min theke :max okkhorer moddhe hote hobe.',
    ],
    'boolean' => 'Ei :attribute field-ti shotto ba mittha hote hobe.',
    'confirmed' => 'Ei :attribute nishchitokoron meleni.',
    'current_password' => 'Password-ti shothik noy.',
    'date' => 'Ei :attribute ekta boidho tarikh noy.',
    'date_equals' => 'Ei :attribute :date er shoman ekta tarikh hote hobe.',
    'date_format' => 'Ei :attribute :format format er shathe mele na.',
    'decimal' => 'Ei :attribute e :decimal doshomik sthan thakte hobe.',
    'declined' => 'Ei :attribute protyakkhan korte hobe.',
    'declined_if' => 'Jokhon :other :value hoy, tokhon ei :attribute protyakkhan korte hobe.',
    'different' => 'Ei :attribute ebong :other alada hote hobe.',
    'digits' => 'Ei :attribute :digits ongker hote hobe.',
    'digits_between' => 'Ei :attribute :min theke :max ongker moddhe hote hobe.',
    'dimensions' => 'Ei :attribute er chobir dimension oboidho.',
    'distinct' => 'Ei :attribute field-e ekta duplicate maan ache.',
    'doesnt_end_with' => 'Ei :attribute nicher egulor kono ekta diye shesh hote parbe na: :values.',
    'doesnt_start_with' => 'Ei :attribute nicher egulor kono ekta diye shuru hote parbe na: :values.',
    'email' => 'Ei :attribute ekta boidho email address hote hobe.',
    'ends_with' => 'Ei :attribute nicher egulor kono ekta diye shesh hote hobe: :values.',
    'enum' => 'Nirbachito :attribute oboidho.',
    'exists' => 'Nirbachito :attribute oboidho.',
    'file' => 'Ei :attribute ekta file hote hobe.',
    'filled' => 'Ei :attribute field-e ekta maan thakte hobe.',
    'gt' => [
        'array' => 'Ei :attribute e :value er cheye beshi item thakte hobe.',
        'file' => 'Ei :attribute :value kilobyte er cheye beshi hote hobe.',
        'numeric' => 'Ei :attribute :value er cheye beshi hote hobe.',
        'string' => 'Ei :attribute :value okkhorer cheye beshi hote hobe.',
    ],
    'gte' => [
        'array' => 'Ei :attribute e :value ti ba tar cheye beshi item thakte hobe.',
        'file' => 'Ei :attribute :value kilobyte er shoman ba tar cheye beshi hote hobe.',
        'numeric' => 'Ei :attribute :value er shoman ba tar cheye beshi hote hobe.',
        'string' => 'Ei :attribute :value okkhorer shoman ba tar cheye beshi hote hobe.',
    ],
    'image' => 'Ei :attribute ekta chobi hote hobe.',
    'in' => 'Nirbachito :attribute oboidho.',
    'in_array' => 'Ei :attribute field-ti :other e nei.',
    'integer' => 'Ei :attribute ekta purno shongkhya hote hobe.',
    'ip' => 'Ei :attribute ekta boidho IP address hote hobe.',
    'ipv4' => 'Ei :attribute ekta boidho IPv4 address hote hobe.',
    'ipv6' => 'Ei :attribute ekta boidho IPv6 address hote hobe.',
    'json' => 'Ei :attribute ekta boidho JSON string hote hobe.',
    'lowercase' => 'Ei :attribute chhotohater okkhore hote hobe.',
    'lt' => [
        'array' => 'Ei :attribute e :value er cheye kom item thakte hobe.',
        'file' => 'Ei :attribute :value kilobyte er cheye kom hote hobe.',
        'numeric' => 'Ei :attribute :value er cheye kom hote hobe.',
        'string' => 'Ei :attribute :value okkhorer cheye kom hote hobe.',
    ],
    'lte' => [
        'array' => 'Ei :attribute e :value er cheye beshi item thakte parbe na.',
        'file' => 'Ei :attribute :value kilobyte er shoman ba tar cheye kom hote hobe.',
        'numeric' => 'Ei :attribute :value er shoman ba tar cheye kom hote hobe.',
        'string' => 'Ei :attribute :value okkhorer shoman ba tar cheye kom hote hobe.',
    ],
    'mac_address' => 'Ei :attribute ekta boidho MAC address hote hobe.',
    'max' => [
        'array' => 'Ei :attribute e :max er cheye beshi item thakte parbe na.',
        'file' => 'Ei :attribute :max kilobyte er cheye beshi hote parbe na.',
        'numeric' => 'Ei :attribute :max er cheye beshi hote parbe na.',
        'string' => 'Ei :attribute :max okkhorer cheye beshi hote parbe na.',
    ],
    'max_digits' => 'Ei :attribute e :max er cheye beshi ongko thakte parbe na.',
    'mimes' => 'Ei :attribute :values dhoroner file hote hobe.',
    'mimetypes' => 'Ei :attribute :values dhoroner file hote hobe.',
    'min' => [
        'array' => 'Ei :attribute e kom pokkhe :min ti item thakte hobe.',
        'file' => 'Ei :attribute kom pokkhe :min kilobyte hote hobe.',
        'numeric' => 'Ei :attribute kom pokkhe :min hote hobe.',
        'string' => 'Ei :attribute kom pokkhe :min okkhorer hote hobe.',
    ],
    'min_digits' => 'Ei :attribute e kom pokkhe :min ti ongko thakte hobe.',
    'multiple_of' => 'Ei :attribute :value er gunitok hote hobe.',
    'not_in' => 'Nirbachito :attribute oboidho.',
    'not_regex' => 'Ei :attribute format-ti oboidho.',
    'numeric' => 'Ei :attribute ekta shongkhya hote hobe.',
    'password' => [
        'letters' => 'Ei :attribute e kom pokkhe ekta okkhor thakte hobe.',
        'mixed' => 'Ei :attribute e kom pokkhe ekta boro hater ebong ekta chhotohater okkhor thakte hobe.',
        'numbers' => 'Ei :attribute e kom pokkhe ekta shongkhya thakte hobe.',
        'symbols' => 'Ei :attribute e kom pokkhe ekta chinhô thakte hobe.',
        'uncompromised' => 'Prodottô :attribute ekta data leak-e paoa geche. Doya kore ekta bhinno :attribute bechhe nin.',
    ],
    'present' => 'Ei :attribute field-ti uposthit thakte hobe.',
    'prohibited' => 'Ei :attribute field-ti nishiddho.',
    'prohibited_if' => 'Jokhon :other :value hoy, tokhon ei :attribute field-ti nishiddho.',
    'prohibited_unless' => 'Jokhon :other :values er moddhe na thake, tokhon ei :attribute field-ti nishiddho.',
    'prohibits' => 'Ei :attribute field-ti :other ke uposthit thakte badha dey.',
    'regex' => 'Ei :attribute format-ti oboidho.',
    'required' => 'Ei :attribute field-ti aboshshok.',
    'required_array_keys' => 'Ei :attribute field-ti :values er jonno entry dhārōn korte hobe.',
    'required_if' => 'Jokhon :other :value hoy, tokhon ei :attribute field-ti aboshshok.',
    'required_if_accepted' => 'Jokhon :other grohon kora hoy, tokhon ei :attribute field-ti aboshshok.',
    'required_unless' => 'Jokhon :other :values er moddhe na thake, tokhon ei :attribute field-ti aboshshok.',
    'required_with' => 'Jokhon :values uposthit thake, tokhon ei :attribute field-ti aboshshok.',
    'required_with_all' => 'Jokhon :values uposthit thake, tokhon ei :attribute field-ti aboshshok.', // Note: Same translation as required_with, but context implies multiple values.
    'required_without' => 'Jokhon :values uposthit na thake, tokhon ei :attribute field-ti aboshshok.',
    'required_without_all' => 'Jokhon :values er konotai uposthit na thake, tokhon ei :attribute field-ti aboshshok.',
    'same' => 'Ei :attribute ebong :other milte hobe.',
    'size' => [
        'array' => 'Ei :attribute e :size ti item thakte hobe.',
        'file' => 'Ei :attribute :size kilobyte hote hobe.',
        'numeric' => 'Ei :attribute :size hote hobe.',
        'string' => 'Ei :attribute :size okkhorer hote hobe.',
    ],
    'starts_with' => 'Ei :attribute nicher egulor kono ekta diye shuru hote hobe: :values.',
    'string' => 'Ei :attribute ekta string hote hobe.',
    'timezone' => 'Ei :attribute ekta boidho timezone hote hobe.',
    'unique' => 'Ei :attribute itimodhye neoa hoyeche.',
    'uploaded' => 'Ei :attribute upload korte byartho hoyeche.',
    'uppercase' => 'Ei :attribute boro hater okkhore hote hobe.',
    'url' => 'Ei :attribute ekta boidho URL hote hobe.',
    'ulid' => 'Ei :attribute ekta boidho ULID hote hobe.',
    'uuid' => 'Ei :attribute ekta boidho UUID hote hobe.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    'custom-messages' => [
        'quantity_not_available' => 'Ache shudhu :qty :unit poriman', // More natural than 'quantity not available'
        'this_field_is_required' => 'Ei field-ti aboshshok',
    ],

];
