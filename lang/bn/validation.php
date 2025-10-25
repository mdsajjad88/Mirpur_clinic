<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (প্রমাণীকরণ ভাষা লাইন)
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    | (নিম্নলিখিত ভাষা লাইনগুলিতে ভ্যালিডেটর ক্লাস দ্বারা ব্যবহৃত ডিফল্ট ত্রুটি বার্তা রয়েছে।
    | এই নিয়মগুলির মধ্যে কয়েকটির একাধিক সংস্করণ রয়েছে যেমন আকারের নিয়ম। এখানে প্রতিটি বার্তা নির্দ্বিধায় পরিবর্তন করুন।)
    |
    */

    'accepted' => ':attribute অবশ্যই গৃহীত হতে হবে।',
    'accepted_if' => ':other যখন :value হয় তখন :attribute অবশ্যই গৃহীত হতে হবে।',
    'active_url' => ':attribute একটি বৈধ URL নয়।',
    'after' => ':attribute অবশ্যই :date এর পরের তারিখ হতে হবে।',
    'after_or_equal' => ':attribute অবশ্যই :date এর পরের বা সমান তারিখ হতে হবে।',
    'alpha' => ':attribute শুধুমাত্র অক্ষর ধারণ করতে পারে।',
    'alpha_dash' => ':attribute শুধুমাত্র অক্ষর, সংখ্যা, ড্যাশ এবং আন্ডারস্কোর ধারণ করতে পারে।',
    'alpha_num' => ':attribute শুধুমাত্র অক্ষর এবং সংখ্যা ধারণ করতে পারে।',
    'array' => ':attribute অবশ্যই একটি অ্যারে হতে হবে।',
    'ascii' => ':attribute অবশ্যই কেবল সিঙ্গল-বাইট আলফানিউমেরিক অক্ষর এবং প্রতীক ধারণ করতে পারে।',
    'before' => ':attribute অবশ্যই :date এর আগের তারিখ হতে হবে।',
    'before_or_equal' => ':attribute অবশ্যই :date এর আগের বা সমান তারিখ হতে হবে।',
    'between' => [
        'array' => ':attribute এর মধ্যে :min এবং :max আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :min এবং :max কিলোবাইটের মধ্যে হতে হবে।',
        'numeric' => ':attribute অবশ্যই :min এবং :max এর মধ্যে হতে হবে।',
        'string' => ':attribute অবশ্যই :min এবং :max অক্ষরের মধ্যে হতে হবে।',
    ],
    'boolean' => ':attribute ক্ষেত্রটি অবশ্যই সত্য বা মিথ্যা হতে হবে।',
    'confirmed' => ':attribute নিশ্চিতকরণ মেলে না।',
    'current_password' => 'পাসওয়ার্ডটি ভুল।',
    'date' => ':attribute একটি বৈধ তারিখ নয়।',
    'date_equals' => ':attribute অবশ্যই :date এর সমান তারিখ হতে হবে।',
    'date_format' => ':attribute ফরম্যাট :format এর সাথে মেলে না।',
    'decimal' => ':attribute এর অবশ্যই :decimal দশমিক স্থান থাকতে হবে।',
    'declined' => ':attribute অবশ্যই প্রত্যাখ্যান করতে হবে।',
    'declined_if' => ':other যখন :value হয় তখন :attribute অবশ্যই প্রত্যাখ্যান করতে হবে।',
    'different' => ':attribute এবং :other অবশ্যই ভিন্ন হতে হবে।',
    'digits' => ':attribute অবশ্যই :digits অংক হতে হবে।',
    'digits_between' => ':attribute অবশ্যই :min এবং :max অংকের মধ্যে হতে হবে।',
    'dimensions' => ':attribute এর ছবির মাত্রা অবৈধ।',
    'distinct' => ':attribute ক্ষেত্রে একটি সদৃশ মান রয়েছে।',
    'doesnt_end_with' => ':attribute নিম্নলিখিতগুলির একটি দিয়ে শেষ নাও হতে পারে: :values।',
    'doesnt_start_with' => ':attribute নিম্নলিখিতগুলির একটি দিয়ে শুরু নাও হতে পারে: :values।',
    'email' => ':attribute অবশ্যই একটি বৈধ ইমেইল ঠিকানা হতে হবে।',
    'ends_with' => ':attribute অবশ্যই নিম্নলিখিতগুলির একটি দিয়ে শেষ হতে হবে: :values।',
    'enum' => 'নির্বাচিত :attribute অবৈধ।',
    'exists' => 'নির্বাচিত :attribute অবৈধ।',
    'file' => ':attribute অবশ্যই একটি ফাইল হতে হবে।',
    'filled' => ':attribute ক্ষেত্রে অবশ্যই একটি মান থাকতে হবে।',
    'gt' => [
        'array' => ':attribute এর অবশ্যই :value এর চেয়ে বেশি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইটের চেয়ে বড় হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value এর চেয়ে বড় হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের চেয়ে বড় হতে হবে।',
    ],
    'gte' => [
        'array' => ':attribute এর অবশ্যই :value টি বা তার বেশি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইট বা তার চেয়ে বড় হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value বা তার চেয়ে বড় হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষর বা তার চেয়ে বড় হতে হবে।',
    ],
    'image' => ':attribute অবশ্যই একটি ছবি হতে হবে।',
    'in' => 'নির্বাচিত :attribute অবৈধ।',
    'in_array' => ':attribute ক্ষেত্রটি :other এ বিদ্যমান নেই।',
    'integer' => ':attribute অবশ্যই একটি পূর্ণসংখ্যা হতে হবে।',
    'ip' => ':attribute অবশ্যই একটি বৈধ আইপি ঠিকানা হতে হবে।',
    'ipv4' => ':attribute অবশ্যই একটি বৈধ IPv4 ঠিকানা হতে হবে।',
    'ipv6' => ':attribute অবশ্যই একটি বৈধ IPv6 ঠিকানা হতে হবে।',
    'json' => ':attribute অবশ্যই একটি বৈধ JSON স্ট্রিং হতে হবে।',
    'lowercase' => ':attribute অবশ্যই ছোট হাতের অক্ষরে হতে হবে।',
    'lt' => [
        'array' => ':attribute এর অবশ্যই :value এর কম আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইটের কম হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value এর কম হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের কম হতে হবে।',
    ],
    'lte' => [
        'array' => ':attribute এর অবশ্যই :value টির বেশি আইটেম থাকতে পারবে না।',
        'file' => ':attribute অবশ্যই :value কিলোবাইট বা তার কম হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value বা তার কম হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষর বা তার কম হতে হবে।',
    ],
    'mac_address' => ':attribute অবশ্যই একটি বৈধ MAC ঠিকানা হতে হবে।',
    'max' => [
        'array' => ':attribute এর অবশ্যই :max টির বেশি আইটেম থাকতে পারবে না।',
        'file' => ':attribute অবশ্যই :max কিলোবাইটের চেয়ে বড় হতে পারবে না।',
        'numeric' => ':attribute অবশ্যই :max এর চেয়ে বড় হতে পারবে না।',
        'string' => ':attribute অবশ্যই :max অক্ষরের চেয়ে বড় হতে পারবে না।',
    ],
    'max_digits' => ':attribute এর অবশ্যই :max অংকের বেশি থাকতে পারবে না।',
    'mimes' => ':attribute অবশ্যই :values টাইপের একটি ফাইল হতে হবে।',
    'mimetypes' => ':attribute অবশ্যই :values টাইপের একটি ফাইল হতে হবে।',
    'min' => [
        'array' => ':attribute এর অবশ্যই কমপক্ষে :min টি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই কমপক্ষে :min কিলোবাইট হতে হবে।',
        'numeric' => ':attribute অবশ্যই কমপক্ষে :min হতে হবে।',
        'string' => ':attribute অবশ্যই কমপক্ষে :min অক্ষর হতে হবে।',
    ],
    'min_digits' => ':attribute এর অবশ্যই কমপক্ষে :min অংক থাকতে হবে।',
    'multiple_of' => ':attribute অবশ্যই :value এর গুণিতক হতে হবে।',
    'not_in' => 'নির্বাচিত :attribute অবৈধ।',
    'not_regex' => ':attribute ফরম্যাট অবৈধ।',
    'numeric' => ':attribute অবশ্যই একটি সংখ্যা হতে হবে।',
    'password' => [
        'letters' => ':attribute তে অবশ্যই অন্তত একটি অক্ষর থাকতে হবে।',
        'mixed' => ':attribute তে অবশ্যই অন্তত একটি বড় হাতের এবং একটি ছোট হাতের অক্ষর থাকতে হবে।',
        'numbers' => ':attribute তে অবশ্যই অন্তত একটি সংখ্যা থাকতে হবে।',
        'symbols' => ':attribute তে অবশ্যই অন্তত একটি প্রতীক থাকতে হবে।',
        'uncompromised' => 'প্রদত্ত :attribute একটি ডেটা ফাঁসে উপস্থিত হয়েছে। অনুগ্রহ করে একটি ভিন্ন :attribute চয়ন করুন।',
    ],
    'present' => ':attribute ক্ষেত্রটি অবশ্যই উপস্থিত থাকতে হবে।',
    'prohibited' => ':attribute ক্ষেত্রটি নিষিদ্ধ।',
    'prohibited_if' => ':other যখন :value হয় তখন :attribute ক্ষেত্রটি নিষিদ্ধ।',
    'prohibited_unless' => ':other যখন :values এর মধ্যে না থাকে তখন :attribute ক্ষেত্রটি নিষিদ্ধ।',
    'prohibits' => ':attribute ক্ষেত্রটি :other কে উপস্থিত হতে বাধা দেয়।',
    'regex' => ':attribute ফরম্যাট অবৈধ।',
    'required' => ':attribute ক্ষেত্রটি আবশ্যক।',
    'required_array_keys' => ':attribute ক্ষেত্রে অবশ্যই :values এর জন্য এন্ট্রি থাকতে হবে।',
    'required_if' => ':other যখন :value হয় তখন :attribute ক্ষেত্রটি আবশ্যক।',
    'required_if_accepted' => ':other গৃহীত হলে :attribute ক্ষেত্রটি আবশ্যক।',
    'required_unless' => ':other যখন :values এর মধ্যে না থাকে তখন :attribute ক্ষেত্রটি আবশ্যক।',
    'required_with' => ':values উপস্থিত থাকলে :attribute ক্ষেত্রটি আবশ্যক।',
    'required_with_all' => ':values উপস্থিত থাকলে :attribute ক্ষেত্রটি আবশ্যক।',
    'required_without' => ':values উপস্থিত না থাকলে :attribute ক্ষেত্রটি আবশ্যক।',
    'required_without_all' => ':values এর কোনওটিই উপস্থিত না থাকলে :attribute ক্ষেত্রটি আবশ্যক।',
    'same' => ':attribute এবং :other অবশ্যই মিলতে হবে।',
    'size' => [
        'array' => ':attribute এ অবশ্যই :size টি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :size কিলোবাইট হতে হবে।',
        'numeric' => ':attribute অবশ্যই :size হতে হবে।',
        'string' => ':attribute অবশ্যই :size অক্ষর হতে হবে।',
    ],
    'starts_with' => ':attribute অবশ্যই নিম্নলিখিতগুলির একটি দিয়ে শুরু হতে হবে: :values।',
    'string' => ':attribute অবশ্যই একটি স্ট্রিং হতে হবে।',
    'timezone' => ':attribute অবশ্যই একটি বৈধ টাইমজোন হতে হবে।',
    'unique' => ':attribute ইতিমধ্যে নেওয়া হয়েছে।',
    'uploaded' => ':attribute আপলোড করতে ব্যর্থ হয়েছে।',
    'uppercase' => ':attribute অবশ্যই বড় হাতের অক্ষরে হতে হবে।',
    'url' => ':attribute অবশ্যই একটি বৈধ URL হতে হবে।',
    'ulid' => ':attribute অবশ্যই একটি বৈধ ULID হতে হবে।',
    'uuid' => ':attribute অবশ্যই একটি বৈধ UUID হতে হবে।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines (কাস্টম প্রমাণীকরণ ভাষা লাইন)
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    | (এখানে আপনি "attribute.rule" কনভেনশন ব্যবহার করে অ্যাট্রিবিউটের জন্য কাস্টম প্রমাণীকরণ বার্তা নির্দিষ্ট করতে পারেন।
    | এটি একটি প্রদত্ত অ্যাট্রিবিউট নিয়মের জন্য একটি নির্দিষ্ট কাস্টম ভাষা লাইন দ্রুত নির্দিষ্ট করতে সাহায্য করে।)
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'কাস্টম-বার্তা',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes (কাস্টম প্রমাণীকরণ অ্যাট্রিবিউট)
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    | (নিম্নলিখিত ভাষা লাইনগুলি আমাদের অ্যাট্রিবিউট স্থানধারককে আরও পাঠক-বান্ধব কিছু দিয়ে অদলবদল করতে ব্যবহৃত হয়
    | যেমন "email" এর পরিবর্তে "ই-মেইল ঠিকানা"। এটি কেবল আমাদের বার্তাটিকে আরও ভাবপূর্ণ করতে সহায়তা করে।)
    |
    */

    'attributes' => [], // You might add Bengali attribute names here later if needed, e.g., 'email' => 'ই-মেইল ঠিকানা'

    'custom-messages' => [
        'quantity_not_available' => ':qty :unit পরিমাণ উপলব্ধ নেই', // Adjusted translation slightly
        'this_field_is_required' => 'এই ক্ষেত্রটি আবশ্যক',
    ],

];
