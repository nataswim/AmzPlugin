<?php

    // Function to generate breadcrumbs
    function ams_fetch_navbar_data() {
        $regions = ams_get_amazon_regions();
        $regionsL = ams_get_amazon_regions_limited();
        $regions_placeholder = get_option('ams_amazon_country');
        foreach ($regions as $key => $value) {
            if ($regions_placeholder == $key) {
                $regions_placeholder = $value['RegionName'];
                break;
            }
        }
        $category_placeholder = get_option('ams_default_category');
        $category_placeholder = $category_placeholder == "_auto_import_amazon" ? "Auto Import From Amazon" : $category_placeholder;
        $store_currency = get_woocommerce_currency();
        $currency_code_options = get_woocommerce_currencies();
        $currency_placeholder = $store_currency;
        foreach ($currency_code_options as $code => $value) {
            if ($store_currency == $code) {
                $currency_placeholder = $value;
                break;
            }
        }

        return array(
            'regions_placeholder' => $regions_placeholder,
            'category_placeholder' => $category_placeholder,
            'currency_placeholder' => $currency_placeholder
        );
    }

    function ams_generate_breadcrumbs() {
        $breadcrumbs = array(
            array('title' => 'Home', 'url' => admin_url('admin.php?page=wc-amazon-affiliate'))
        );

        $current_page = isset($_GET['page']) ? $_GET['page'] : '';
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : '';

        switch ($current_page) {
            case 'wc-amazon-affiliate':
                $breadcrumbs[] = array('title' => 'Dashboard', 'url' => '');
                break;
            case 'wc-product-search':
                $breadcrumbs[] = array('title' => 'Import Product (API)', 'url' => '');
                break;
            case 'product-import-by-url':
                $breadcrumbs[] = array('title' => 'Import by URL', 'url' => '');
                break;
            case 'products-search-without-api':
                $breadcrumbs[] = array('title' => 'Search and Import', 'url' => '');
                break;
            case 'product-review-import':
                $breadcrumbs[] = array('title' => 'Review Import', 'url' => '');
                break;
            case 'wc-product-setting-page':
                $breadcrumbs[] = array('title' => 'Settings', 'url' => '');
                if ($current_tab) {
                    switch ($current_tab) {
                        case 'pills-general-tab':
                            $breadcrumbs[] = array('title' => 'Configuration', 'url' => '');
                            break;
                        case 'pills-az-settings-tab':
                            $breadcrumbs[] = array('title' => 'Amazon API Setting', 'url' => '');
                            break;
                        case 'pills-az-products-tab':
                            $breadcrumbs[] = array('title' => 'Auto Products Update', 'url' => '');
                            break;
                    }
                }
                break;
        }

        return $breadcrumbs;
    }

    function get_wc_terms() {
        $categories = get_terms( array(
            'hide_empty' => false,
        ) );
        $cat = array();
        foreach ( $categories as $row ) {
            if ( $row->slug == "uncategorized" ) continue;
            if ( 'product_cat' === $row->taxonomy ) {
                $cat[] = array(
                    'term_id'  => $row->term_id,
                    'name'  => $row->name,
                );
            }
        }
        return array_reverse($cat);
    }

    /**
     * Define amazon RegionName and host and also RegionCode
     *
     * @return string[][]
     */
    function ams_get_amazon_regions() {
        $regions = array(
            'com.au' => array('RegionName' => 'Australia', 'Host' => 'webservices.amazon.com.au', 'RegionCode' => 'us-west-2'),
            'com.br' => array('RegionName' => 'Brazil', 'Host' => 'webservices.amazon.com.br', 'RegionCode' => 'us-east-1'),
            'ca' => array('RegionName' => 'Canada', 'Host' => 'webservices.amazon.ca', 'RegionCode' => 'us-east-1'),
            'cn' => array('RegionName' => 'China', 'Host' => 'webservices.amazon.cn', 'RegionCode' => 'us-west-2'),
            'fr' => array('RegionName' => 'France', 'Host' => 'webservices.amazon.fr', 'RegionCode' => 'eu-west-1'),
            'de' => array('RegionName' => 'Germany', 'Host' => 'webservices.amazon.de', 'RegionCode' => 'eu-west-1'),
            'in' => array('RegionName' => 'India', 'Host' => 'webservices.amazon.in', 'RegionCode' => 'eu-west-1'),
            'it' => array('RegionName' => 'Italy', 'Host' => 'webservices.amazon.it', 'RegionCode' => 'eu-west-1'),
            'jp' => array('RegionName' => 'Japan', 'Host' => 'webservices.amazon.co.jp', 'RegionCode' => 'us-west-2'),
            'mx' => array('RegionName' => 'Mexico', 'Host' => 'webservices.amazon.com.mx', 'RegionCode' => 'us-east-1'),
            'nl' => array('RegionName' => 'Netherlands', 'Host' => 'webservices.amazon.nl', 'RegionCode' => 'eu-west-1'),
            'sa' => array('RegionName' => 'Saudi Arabia', 'Host' => 'webservices.amazon.sa', 'RegionCode' => 'eu-west-1'),
            'sg' => array('RegionName' => 'Singapore', 'Host' => 'webservices.amazon.sg', 'RegionCode' => 'us-west-2'),
            'es' => array('RegionName' => 'Spain', 'Host' => 'webservices.amazon.es', 'RegionCode' => 'eu-west-1'),
            'com.tr' => array('RegionName' => 'Turkey', 'Host' => 'webservices.amazon.com.tr', 'RegionCode' => 'eu-west-1'),
            'ae' => array('RegionName' => 'United Arab Emirates', 'Host' => 'webservices.amazon.ae', 'RegionCode' => 'eu-west-1'),
            'co.uk' => array('RegionName' => 'United Kingdom', 'Host' => 'webservices.amazon.co.uk', 'RegionCode' => 'eu-west-1'),
            'com' => array('RegionName' => 'United States', 'Host' => 'webservices.amazon.com', 'RegionCode' => 'us-east-1'),
            'pl' => array('RegionName' => 'Poland', 'Host' => 'webservices.amazon.pl', 'RegionCode' => 'eu-west-1'),
        );
        return $regions;
    }

    function ams_get_amazon_regions_limited() {
        $regions = array(
            'ca' => array('RegionName' => 'Canada', 'Host' => 'webservices.amazon.ca', 'RegionCode' => 'us-east-1'),
            'fr' => array('RegionName' => 'France', 'Host' => 'webservices.amazon.fr', 'RegionCode' => 'eu-west-1'),
            'de' => array('RegionName' => 'Germany', 'Host' => 'webservices.amazon.de', 'RegionCode' => 'eu-west-1'),
            'jp' => array('RegionName' => 'Japan', 'Host' => 'webservices.amazon.co.jp', 'RegionCode' => 'us-west-2'),
            'co.uk' => array('RegionName' => 'United Kingdom', 'Host' => 'webservices.amazon.co.uk', 'RegionCode' => 'eu-west-1'),
            'com' => array('RegionName' => 'United States', 'Host' => 'webservices.amazon.com', 'RegionCode' => 'us-east-1'),
        );
        return $regions;
    }

    function no_api_active_country_url() {
        $default = 'com';
        $selected_country = get_option('ams_amazon_country') ?? $default;

        $regions = [
            'ca'        =>      'https://www.amazon.ca',
            'cn'        =>      'https://www.amazon.com',
            'fr'        =>      'https://www.amazon.fr',
            'de'        =>      'https://www.amazon.de',
            'in'        =>      'https://www.amazon.in',
            'it'        =>      'https://www.amazon.it',
            'nl'        =>      'https://www.amazon.nl',
            'sa'        =>      'https://www.amazon.sa',
            'sg'        =>      'https://www.amazon.sg',
            'es'        =>      'https://www.amazon.es',
            'ae'        =>      'https://www.amazon.ae',
            'pl'        =>      'https://www.amazon.pl',
            'com'       =>      'https://www.amazon.com',
            'jp'        =>      'https://www.amazon.co.jp',
            'mx'        =>      'https://www.amazon.com.mx',
            'co.uk'     =>      'https://www.amazon.co.uk',
            'com.au'    =>      'https://www.amazon.com.au',
            'com.br'    =>      'https://www.amazon.com.br',
            'com.tr'    =>      'https://www.amazon.com.tr',
        ];

        if( array_key_exists($selected_country, $regions) ) {
            return $regions[$selected_country];
        }

        return $regions[$default];
    }

    /**
     * Total products information is brought through this function
     *
     * @return array
     */
    function ams_get_all_products_info()
    {
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT $wpdb->posts.ID, $wpdb->postmeta.meta_value
                 FROM $wpdb->posts, $wpdb->postmeta
                 WHERE $wpdb->posts.ID  = $wpdb->postmeta.post_id
                 AND $wpdb->posts.post_type  = %s
                 AND $wpdb->postmeta.meta_key = %s
                 ",
                'product', '_wca_amazon_affiliate_asin'
            )
        );
        $products_search_count = get_option('wca_products_search_count');
        $data = array();
        $data['asin'] = [];
        $total_view_count = 0;
        $total_product_direct_redirected = 0;
        $total_product_added_to_cart = 0;
        foreach ($results as $row) {
            if(empty($row->meta_value)) continue;
            $data['asin'][] = $row->meta_value;
            $data['id'][] = $row->ID;
            $data['product_id'][$row->ID]['method'] = get_post_meta($row->ID, '_import_method', true);
            $data['product_id'][$row->ID]['region'] = get_post_meta($row->ID, '_region', true);
            $data['product_id'][$row->ID]['url'] = get_post_meta($row->ID, '_detail_page_url', true);
            $view = get_post_meta($row->ID, 'ams_product_views_count', true);
            $total_view_count = $total_view_count + (int) $view;
            $product_direct_redirected = get_post_meta($row->ID, 'ams_product_direct_redirected', true);
            $total_product_direct_redirected = $total_product_direct_redirected + (int) $product_direct_redirected;
            $product_added_to_cart = get_post_meta($row->ID, 'ams_product_added_to_cart', true);
            $total_product_added_to_cart = $total_product_added_to_cart + (int) $product_added_to_cart;
        }
        $data['total_view_count'] = $total_view_count;
        $data['total_product_direct_redirected'] = $total_product_direct_redirected;
        $data['total_product_added_to_cart'] = $total_product_added_to_cart;
        $data['products_count'] = (isset($data['product_id'])) ? sizeof($data['product_id']) : 0;
        $data['products_search_count'] = $products_search_count;
        return $data;
    }

    /**
     * Total products information is brought through this function
     *
     * @return array
     */
    function ams_get_all_products_info_with_parent()
    {
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT $wpdb->posts.ID, $wpdb->postmeta.meta_value
                 FROM $wpdb->posts, $wpdb->postmeta
                 WHERE $wpdb->posts.ID  = $wpdb->postmeta.post_id
                 AND $wpdb->posts.post_type  = %s
                 AND ( $wpdb->postmeta.meta_key = %s || $wpdb->postmeta.meta_key = %s )
                 ",
                'product', '_wca_amazon_affiliate_asin', '_wca_amazon_affiliate_parent_asin'
            )
        );
        $products_search_count = get_option('wca_products_search_count');
        $data = array();
        $data['asin'] = [];
        $total_view_count = 0;
        $total_product_direct_redirected = 0;
        $total_product_added_to_cart = 0;
        foreach ($results as $row) {
            if(empty($row->meta_value)) continue;
            $data['asin'][] = $row->meta_value;
            $data['id'][] = $row->ID;
            $data['product_id'][$row->ID]['method'] = get_post_meta($row->ID, '_import_method', true);
            $data['product_id'][$row->ID]['region'] = get_post_meta($row->ID, '_region', true);
            $data['product_id'][$row->ID]['url'] = get_post_meta($row->ID, '_detail_page_url', true);
            $view = get_post_meta($row->ID, 'ams_product_views_count', true);
            $total_view_count = $total_view_count + (int) $view;
            $product_direct_redirected = get_post_meta($row->ID, 'ams_product_direct_redirected', true);
            $total_product_direct_redirected = $total_product_direct_redirected + (int) $product_direct_redirected;
            $product_added_to_cart = get_post_meta($row->ID, 'ams_product_added_to_cart', true);
            $total_product_added_to_cart = $total_product_added_to_cart + (int) $product_added_to_cart;
        }
        $data['total_view_count'] = $total_view_count;
        $data['total_product_direct_redirected'] = $total_product_direct_redirected;
        $data['total_product_added_to_cart'] = $total_product_added_to_cart;
        $data['products_count'] = (isset($data['product_id'])) ? sizeof($data['product_id']) : 0;
        $data['products_search_count'] = $products_search_count;
        return $data;
    }

    /**
     * Amazon affiliate departments country base
     *
     * @return string[][]
     */
    function ams_amazon_departments()
    {
        $cat = array(
            'com.au' => array(
                'All' => 'All Departments',
                'Automotive' => 'Automotive',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Computers' => 'Computers',
                'Electronics' => 'Electronics',
                'EverythingElse' => 'Everything Else',
                'Fashion' => 'Clothing & Shoes',
                'GiftCards' => 'Gift Cards',
                'HealthPersonalCare' => 'Health, Household & Personal Care',
                'HomeAndKitchen' => 'Home & Kitchen',
                'KindleStore' => 'Kindle Store',
                'Lighting' => 'Lighting',
                'Luggage' => 'Luggage & Travel Gear',
                'MobileApps' => 'Apps & Games',
                'MoviesAndTV' => 'Movies & TV',
                'Music' => 'CDs & Vinyl',
                'OfficeProducts' => 'Stationery & Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports, Fitness & Outdoors',
                'ToolsAndHomeImprovement' => 'Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VideoGames' => 'Video Games',
            ),
            'com.br' => array(
                'All' => 'Todos os departamentos',
                'Books' => 'Livros',
                'Computers' => 'Computadores e Informática',
                'Electronics' => 'Eletrônicos',
                'HomeAndKitchen' => 'Casa e Cozinha',
                'KindleStore' => 'Loja Kindle',
                'MobileApps' => 'Apps e Jogos',
                'OfficeProducts' => 'Material para Escritório e Papelaria',
                'ToolsAndHomeImprovement' => 'Ferramentas e Materiais de Construção',
                'VideoGames' => 'Games',
            ),
            'ca' => array(
                'All' => 'All Department',
                'Apparel' => 'Clothing & Accessories',
                'Automotive' => 'Automotive',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Classical' => 'Classical Music',
                'Electronics' => 'Electronics',
                'EverythingElse' => 'Everything Else',
                'ForeignBooks' => 'English Books',
                'GardenAndOutdoor' => 'Patio, Lawn & Garden',
                'GiftCards' => 'GiftCards',
                'GroceryAndGourmetFood' => 'Grocery & Gourmet Food',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Health & Personal Care',
                'HomeAndKitchen' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Jewelry',
                'KindleStore' => 'Kindle Store',
                'Luggage' => 'Luggage & Bags',
                'LuxuryBeauty' => 'Luxury Beauty',
                'MobileApps' => 'Apps & Games',
                'MoviesAndTV' => 'Movies & TV',
                'Music' => 'Music',
                'MusicalInstruments' => 'Musical Instruments, Stage & Studio',
                'OfficeProducts' => 'Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Shoes' => 'Shoes & Handbags',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports & Outdoors',
                'ToolsAndHomeImprovement' => 'Tools & Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VHS' => 'VHS',
                'VideoGames' => 'Video Games',
                'Watches' => 'Watches',
            ),
            // 'cn' => array(
            //     'All' => 'All Departments',
            // ),
            'fr' => array(
                'All' => 'Toutes nos catégories',
                'Apparel' => 'Vêtements et accessoires',
                'Appliances' => 'Gros électroménager',
                'Automotive' => 'Auto et Moto',
                'Baby' => '  Bébés & Puériculture',
                'Beauty' => 'Beauté et Parfum',
                'Books' => 'Livres en français',
                'Computers' => 'Informatique',
                'DigitalMusic' => 'Téléchargement de musique',
                'Electronics' => 'High-Tech',
                'EverythingElse' => 'Autres',
                'Fashion' => 'Mode',
                'ForeignBooks' => 'Livres anglais et étrangers',
                'GardenAndOutdoor' => 'Jardin',
                'GiftCards' => 'Boutique chèques-cadeaux',
                'GroceryAndGourmetFood' => 'Epicerie',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Hygiène et Santé',
                'HomeAndKitchen' => 'Cuisine & Maison',
                'Industrial' => 'Secteur industriel & scientifique',
                'Jewelry' => 'Bijoux',
                'KindleStore' => 'Boutique Kindle',
                'Lighting' => 'Luminaires et Eclairage',
                'Luggage' => 'Bagages',
                'LuxuryBeauty' => 'Beauté Prestige',
                'MobileApps' => 'Applis & Jeux',
                'MoviesAndTV' => 'DVD & Blu-ray',
                'Music' => 'Musique : CD & Vinyles',
                'MusicalInstruments' => 'Instruments de musique & Sono',
                'OfficeProducts' => 'Fournitures de bureau',
                'PetSupplies' => 'Animalerie',
                'Shoes' => 'Chaussures et Sacs',
                'Software' => 'Logiciels',
                'SportsAndOutdoors' => 'Sports et Loisirs',
                'ToolsAndHomeImprovement' => 'Bricolage',
                'ToysAndGames' => 'Jeux et Jouets',
                'VHS' => 'VHS',
                'VideoGames' => 'Jeux vidéo',
                'Watches' => 'Montres',
            ),
            'de' => array(
                'All' => 'Alle Kategorien',
                'AmazonVideo' => 'Prime Video',
                'Apparel' => 'Bekleidung',
                'Appliances' => 'Elektro-Großgeräte',
                'Automotive' => 'Auto & Motorrad',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Bücher',
                'Classical' => 'Klassik',
                'Computers' => 'Computer & Zubehör',
                'DigitalMusic' => 'Musik-Downloads',
                'Electronics' => 'Elektronik & Foto',
                'EverythingElse' => 'Sonstiges',
                'Fashion' => 'Fashion',
                'ForeignBooks' => 'Bücher (Fremdsprachig)',
                'GardenAndOutdoor' => 'Garten',
                'GiftCards' => 'Geschenkgutscheine',
                'GroceryAndGourmetFood' => 'Lebensmittel & Getränke',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Drogerie & Körperpflege',
                'HomeAndKitchen' => 'Küche, Haushalt & Wohnen',
                'Industrial' => 'Gewerbe, Industrie & Wissenschaft',
                'Jewelry' => 'Schmuck',
                'KindleStore' => 'Kindle-Shop',
                'Lighting' => 'Beleuchtung',
                'Luggage' => 'Koffer, Rucksäcke & Taschen',
                'LuxuryBeauty' => 'Luxury Beauty',
                'Magazines' => 'Zeitschriften',
                'MobileApps' => 'Apps & Spiele',
                'MoviesAndTV' => 'DVD & Blu-ray',
                'Music' => 'Musik-CDs & Vinyl',
                'MusicalInstruments' => 'Musikinstrumente & DJ-Equipment',
                'OfficeProducts' => 'Bürobedarf & Schreibwaren',
                'PetSupplies' => 'Haustier',
                'Photo' => 'Kamera & Foto',
                'Shoes' => 'Schuhe & Handtaschen',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sport & Freizeit',
                'ToolsAndHomeImprovement' => 'Baumarkt',
                'ToysAndGames' => 'Spielzeug',
                'VHS' => 'VHS',
                'VideoGames' => 'Games',
                'Watches' => 'Uhren',
            ),
            'in' => array(
                'All' => 'All Categories',
                'Apparel' => 'Clothing & Accessories',
                'Appliances' => 'Appliances',
                'Automotive' => 'Car & Motorbike',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Collectibles' => 'Collectibles',
                'Computers' => 'Computers & Accessories',
                'Electronics' => 'Electronics',
                'EverythingElse' => 'Everything Else',
                'Fashion' => 'Amazon Fashion',
                'Furniture' => 'Furniture',
                'GardenAndOutdoor' => 'Garden & Outdoors',
                'GiftCards' => 'Gift Cards',
                'GroceryAndGourmetFood' => 'Grocery & Gourmet Foods',
                'HealthPersonalCare' => 'Health & Personal Care',
                'HomeAndKitchen' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Jewellery',
                'KindleStore' => 'Kindle Store',
                'Luggage' => 'Luggage & Bags',
                'LuxuryBeauty' => 'Luxury Beauty',
                'MobileApps' => 'Apps & Games',
                'MoviesAndTV' => 'Movies & TV Shows',
                'Music' => 'Music',
                'MusicalInstruments' => 'Musical Instruments',
                'OfficeProducts' => 'Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports, Fitness & Outdoors',
                'ToolsAndHomeImprovement' => 'Tools & Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VideoGames' => 'Video Games',
                'Watches' => 'Watches',
            ),
            'it' => array(
                'All' => 'Tutte le categorie',
                'Apparel' => 'Abbigliamento',
                'Appliances' => 'Grandi elettrodomestici',
                'Automotive' => 'Auto e Moto',
                'Baby' => 'Prima infanzia',
                'Beauty' => 'Bellezza',
                'Books' => 'Libri',
                'Computers' => 'Informatica',
                'DigitalMusic' => 'Musica Digitale',
                'Electronics' => 'Elettronica',
                'EverythingElse' => 'Altro',
                'Fashion' => 'Moda',
                'ForeignBooks' => 'Libri in altre lingue',
                'GardenAndOutdoor' => 'Giardino e giardinaggio',
                'GiftCards' => 'Buoni Regalo',
                'GroceryAndGourmetFood' => 'Alimentari e cura della casa',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Salute e cura della persona',
                'HomeAndKitchen' => 'Casa e cucina',
                'Industrial' => 'Industria e Scienza',
                'Jewelry' => 'Gioielli',
                'KindleStore' => 'Kindle Store',
                'Lighting' => 'Illuminazione',
                'Luggage' => 'Valigeria',
                'MobileApps' => 'App e Giochi',
                'MoviesAndTV' => 'Film e TV',
                'Music' => 'CD e Vinili',
                'MusicalInstruments' => 'Strumenti musicali e DJ',
                'OfficeProducts' => 'Cancelleria e prodotti per ufficio',
                'PetSupplies' => 'Prodotti per animali domestici',
                'Shoes' => 'Scarpe e borse',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sport e tempo libero',
                'ToolsAndHomeImprovement' => 'Fai da te',
                'ToysAndGames' => 'Giochi e giocattoli',
                'VideoGames' => 'Videogiochi',
                'Watches' => 'Orologi',
            ),
            'jp' => array(
                'All' => 'All Departments',
                'AmazonVideo' => 'Prime Video',
                'Apparel' => 'Clothing & Accessories',
                'Appliances' => 'Large Appliances',
                'Automotive' => 'Car & Bike Products',
                'Baby' => 'Baby & Maternity',
                'Beauty' => 'Beauty',
                'Books' => 'Japanese Books',
                'Computers' => 'Computers & Accessories',
                'CreditCards' => 'Credit Cards',
                'DigitalMusic' => 'Digital Music',
                'Electronics' => 'Electronics & Cameras',
                'EverythingElse' => 'Everything Else',
                'Fashion' => 'Fashion',
                'FashionBaby' => 'Kids & Baby',
                'FashionMen' => 'Men',
                'FashionWomen' => 'Women',
                'ForeignBooks' => 'English Books',
                'GiftCards' => 'Gift Cards',
                'GroceryAndGourmetFood' => 'Food & Beverage',
                'HealthPersonalCare' => 'Health & Personal Care',
                'Hobbies' => 'Hobby',
                'HomeAndKitchen' => 'Kitchen & Housewares',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Jewelry',
                'KindleStore' => 'Kindle Store',
                'MobileApps' => 'Apps & Games',
                'MoviesAndTV' => 'Movies & TV',
                'Music' => 'Music',
                'MusicalInstruments' => 'Musical Instruments',
                'OfficeProducts' => 'Stationery and Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Shoes' => 'Shoes & Bags',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports',
                'ToolsAndHomeImprovement' => 'DIY, Tools & Garden',
                'Toys' => 'Toys',
                'VideoGames' => 'Computer & Video Games',
                'Watches' => 'Watches',
            ),
            'mx' => array(
                'All' => 'Todos los departamentos',
                'Automotive' => 'Auto',
                'Baby' => 'Bebé',
                'Books' => 'Libros',
                'Electronics' => 'Electrónicos',
                'Fashion' => 'Ropa, Zapatos y Accesorios',
                'FashionBaby' => 'Ropa, Zapatos y Accesorios Bebé',
                'FashionBoys' => 'Ropa, Zapatos y Accesorios Niños',
                'FashionGirls' => 'Ropa, Zapatos y Accesorios Niñas',
                'FashionMen' => 'Ropa, Zapatos y Accesorios Niñas',
                'FashionWomen' => 'Ropa, Zapatos y Accesorios Mujeres',
                'GroceryAndGourmetFood' => 'Alimentos y Bebidas',
                'Handmade' => 'Productos Handmade',
                'HealthPersonalCare' => 'Salud, Belleza y Cuidado Personal',
                'HomeAndKitchen' => 'Hogar y Cocina',
                'IndustrialAndScientific' => 'Industria y ciencia',
                'KindleStore' => 'Tienda Kindle',
                'MoviesAndTV' => 'Películas y Series de TV',
                'Music' => 'Música',
                'MusicalInstruments' => 'Instrumentos musicales',
                'OfficeProducts' => 'Oficina y Papelería',
                'PetSupplies' => 'Mascotas',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Deportes y Aire Libre',
                'ToolsAndHomeImprovement' => 'Herramientas y Mejoras del Hogar',
                'ToysAndGames' => 'Juegos y juguetes',
                'VideoGames' => 'Videojuegos',
                'Watches' => 'Relojes',
            ),
            'nl' => array(
                'All' => 'Alle afdelingen',
                'Automotive' => 'Auto en motor',
                'Baby' => 'Babyproducten',
                'Beauty' => 'Beauty en persoonlijke verzorging',
                'Books' => 'Boeken',
                'Electronics' => 'Elektronica',
                'EverythingElse' => 'Overig',
                'Fashion' => 'Kleding, schoenen en sieraden',
                'GardenAndOutdoor' => 'Tuin, terras en gazon',
                'GiftCards' => 'Cadeaubonnen',
                'GroceryAndGourmetFood' => 'Levensmiddelen',
                'HealthPersonalCare' => 'Gezondheid en persoonlijke verzorging',
                'HomeAndKitchen' => 'Wonen en keuken',
                'Industrial' => 'Zakelijk, industrie en wetenschap',
                'KindleStore' => 'Kindle Store',
                'MoviesAndTV' => 'Films en tv',
                'Music' => 'Cd\'s en lp\'s',
                'MusicalInstruments' => 'Muziekinstrumenten',
                'OfficeProducts' => 'Kantoorproducten',
                'PetSupplies' => 'Huisdierbenodigdheden',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sport en outdoor',
                'ToolsAndHomeImprovement' => 'Klussen en gereedschap',
                'ToysAndGames' => 'Speelgoed en spellen',
                'VideoGames' => 'Videogames',
            ),
            'sa' => array(
                'All' => 'All Categories',
                'ArtsAndCrafts' => 'Arts, Crafts & Sewing',
                'Automotive' => 'Automotive Parts & Accessories',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty & Personal Care',
                'Books' => 'Books',
                'Computers' => 'Computer & Accessories',
                'Electronics' => 'Electronics',
                'Fashion' => 'Clothing, Shoes & Jewelry',
                'GardenAndOutdoor' => 'Home & Garden',
                'GiftCards' => 'Gift Cards',
                'GroceryAndGourmetFood' => 'Grocery & Gourmet Food',
                'HealthPersonalCare' => 'Health, Household & Baby Care',
                'HomeAndKitchen' => 'Kitchen & Dining',
                'Industrial' => 'Industrial & Scientific',
                'KindleStore' => 'Kindle Store',
                'Miscellaneous' => 'Everything Else',
                'MoviesAndTV' => 'MoviesAndTV',
                'Music' => 'CDs & Vinyl',
                'MusicalInstruments ' => 'Musical Instruments',
                'OfficeProducts' => 'Office Productsd',
                'PetSupplies' => 'Pet Supplies',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports',
                'ToolsAndHomeImprovement' => 'Tools & Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VideoGames' => 'Video Games',
            ),
            'sg' => array(
                'All' => 'All Departments',
                'Automotive' => 'Automotive',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty & Personal Care',
                'Computers' => 'Computers',
                'Electronics' => 'Electronics',
                'GroceryAndGourmetFood' => 'Grocery',
                'HealthPersonalCare' => 'HealthPersonalCare',
                'HomeAndKitchen' => 'Home, Kitchen & Dining',
                'OfficeProducts' => 'Office Products',
                'PetSupplies' => 'Pet Supplies',
                'SportsAndOutdoors' => 'Sports & Outdoors',
                'ToolsAndHomeImprovement' => 'Tools & Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VideoGames' => 'Video Games',
            ),
            'es' => array(
                'All' => 'Todos los departamentos',
                'Apparel' => 'Ropa y accesorios',
                'Appliances' => 'Appliances',
                'Automotive' => 'Coche y moto',
                'Baby' => 'Bebé',
                'Beauty' => 'Belleza',
                'Books' => 'Libros',
                'Computers' => 'Informática',
                'DigitalMusic' => 'Música Digital',
                'Electronics' => 'Electrónica',
                'EverythingElse' => 'Otros Productos',
                'Fashion' => 'Moda',
                'ForeignBooks' => 'Libros en idiomas extranjeros',
                'GardenAndOutdoor' => 'Jardín',
                'GiftCards' => 'Cheques regalo',
                'GroceryAndGourmetFood' => 'Alimentación y bebidas',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Salud y cuidado personal',
                'HomeAndKitchen' => 'Hogar y cocina',
                'Industrial' => 'Industria y ciencia',
                'Jewelry' => 'Joyería',
                'KindleStore' => 'Tienda Kindle',
                'Lighting' => 'Iluminación',
                'Luggage' => 'Equipaje',
                'MobileApps' => 'Appstore para Android',
                'MoviesAndTV' => 'Películas y TV',
                'Music' => 'Música: CDs y vinilos',
                'MusicalInstruments ' => 'Instrumentos musicales',
                'OfficeProducts' => 'Oficina y papelería',
                'PetSupplies' => 'Productos para mascotas',
                'Shoes' => 'Zapatos y complementos',
                'Software' => 'Softwares',
                'SportsAndOutdoors' => 'Deportes y aire libre',
                'ToolsAndHomeImprovement' => 'Bricolaje y herramientas',
                'ToysAndGames' => 'Juguetes y juegos',
                'Vehicles' => 'Coche - renting',
                'VideoGames' => 'Videojuegos',
                'Watches' => 'Relojes',
            ),
            'com.tr' => array(
                'All' => 'Tüm Kategoriler',
                'Baby' => 'Bebek',
                'Books' => 'Kitaplar',
                'Computers' => 'Bilgisayarlar',
                'Electronics' => 'Elektronik',
                'EverythingElse' => 'Diğer Her Şey',
                'Fashion' => 'Moda',
                'HomeAndKitchen' => 'Ev ve Mutfak',
                'OfficeProducts' => 'Ofis Ürünleri',
                'SportsAndOutdoors' => 'Spor',
                'ToolsAndHomeImprovement' => 'Yapı Market',
                'ToysAndGames' => 'Oyuncaklar ve Oyunlar',
                'VideoGames' => 'PC ve Video Oyunları',
            ),
            'ae' => array(
                'All' => 'All Departments',
                'Appliances' => 'Appliances',
                'ArtsAndCrafts' => 'Arts, Crafts & Sewing',
                'Automotive' => 'Automotive Parts & Accessories',
                'Baby' => 'Baby',
                'Beauty ' => 'Beauty & Personal Care',
                'Books' => 'Books',
                'Computers' => 'Computers',
                'Electronics' => 'Electronics',
                'EverythingElse' => 'Everything Else',
                'Fashion' => 'Clothing, Shoes & Jewelry',
                'GardenAndOutdoor' => 'Home & Garden',
                'GroceryAndGourmetFood' => 'Grocery & Gourmet Food',
                'HealthPersonalCare' => 'Health, Household & Baby Care',
                'HomeAndKitchen' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'Lighting' => 'Lighting',
                'MusicalInstruments' => 'Musical Instruments',
                'OfficeProducts' => 'Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports',
                'ToolsAndHomeImprovement' => 'Tools & Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VideoGames' => 'Video Games',
            ),
            'co.uk' => array(
                'All' => 'All Departments',
                'AmazonVideo' => 'Amazon Video',
                'Apparel' => 'Clothing',
                'Appliances' => 'Large Appliances',
                'Automotive' => 'Car & Motorbike',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Classical' => 'Classical Music',
                'Computers' => 'Computers & Accessories',
                'DigitalMusic' => 'Digital Music',
                'Electronics' => 'Electronics & Photo',
                'EverythingElse' => 'Everything Else',
                'Fashion' => 'Fashion',
                'GardenAndOutdoor' => 'Garden & Outdoors',
                'GiftCards' => 'Gift Cards',
                'GroceryAndGourmetFood' => 'Grocery',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Health & Personal Care',
                'HomeAndKitchen' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Industrial & Scientific',
                'KindleStore' => 'Kindle Store',
                'Luggage' => 'Luggage',
                'LuxuryBeauty' => 'Luxury Beauty',
                'MobileApps' => 'Apps & Games',
                'MoviesAndTV' => 'DVD & Blu-ray',
                'Music' => 'CDs & Vinyl',
                'MusicalInstruments' => 'Musical Instruments & DJ',
                'OfficeProducts' => 'Stationery & Office SuppliesJ',
                'PetSupplies' => 'Pet Supplies',
                'Shoes' => 'Shoes & Bags',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports & Outdoors',
                'ToolsAndHomeImprovement' => 'DIY & Tools',
                'ToysAndGames' => 'Toys & Games',
                'VHS' => 'VHS',
                'VideoGames' => 'PC & Video Games',
                'Watches' => 'Watches',
            ),
            'com' => array(
                'All' => 'All Departments',
                'AmazonVideo' => 'Prime Video',
                'Apparel' => 'Clothing & Accessories',
                'Appliances' => 'Appliances',
                'ArtsAndCrafts' => 'Arts, Crafts & Sewing',
                'Automotive' => 'Automotive Parts & Accessories',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty & Personal Care',
                'Books' => 'Books',
                'Classical' => 'Classical',
                'Collectibles' => 'Collectibles & Fine Art',
                'Computers' => 'Computers',
                'DigitalMusic' => 'Digital Music',
                'DigitalEducationalResources' => 'Digital Educational Resources',
                'Electronics' => 'Electronics',
                'EverythingElse' => 'Everything Else',
                'Fashion' => 'Clothing, Shoes & Jewelry',
                'FashionBaby' => 'Clothing, Shoes & Jewelry Baby',
                'FashionBoys' => 'Clothing, Shoes & Jewelry Boys',
                'FashionGirls' => 'Clothing, Shoes & Jewelry Girls',
                'FashionMen' => 'Clothing, Shoes & Jewelry Men',
                'FashionWomen' => 'Clothing, Shoes & Jewelry Women',
                'GardenAndOutdoor' => 'Garden & Outdoor',
                'GiftCards' => 'Gift Cards',
                'GroceryAndGourmetFood' => 'Grocery & Gourmet Food',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Health, Household & Baby Care',
                'HomeAndKitchen' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Jewelry',
                'KindleStore' => 'Kindle Store',
                'LocalServices' => 'Home & Business Services',
                'Luggage' => 'Luggage & Travel Gear',
                'LuxuryBeauty' => 'Luxury Beauty',
                'Magazines  ' => 'Magazine Subscriptions',
                'MobileAndAccessories' => 'Cell Phones & Accessories',
                'MobileApps' => 'Apps & Gamess',
                'MoviesAndTV' => 'Movies & TV',
                'Music' => 'CDs & Vinyl',
                'MusicalInstruments' => 'Musical Instruments',
                'OfficeProducts' => 'Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Photo' => 'Camera & Photo',
                'Shoes' => 'Shoes',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports & Outdoors',
                'ToolsAndHomeImprovement' => 'Tools & Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VHS' => 'VHS',
                'VideoGames' => 'Video Games',
                'Watches' => 'Watches',
            ),
            'pl' => array(
                'All' => 'All Departments',
                'AmazonVideo' => 'Prime Video',
                'Apparel' => 'Clothing & Accessories',
                'Appliances' => 'Appliances',
                'ArtsAndCrafts' => 'Arts, Crafts & Sewing',
                'Automotive' => 'Automotive Parts & Accessories',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty & Personal Care',
                'Books' => 'Books',
                'Classical' => 'Classical',
                'Collectibles' => 'Collectibles & Fine Art',
                'Computers' => 'Computers',
                'DigitalMusic' => 'Digital Music',
                'DigitalEducationalResources' => 'Digital Educational Resources',
                'Electronics' => 'Electronics',
                'EverythingElse' => 'Everything Else',
                'Fashion' => 'Clothing, Shoes & Jewelry',
                'FashionBaby' => 'Clothing, Shoes & Jewelry Baby',
                'FashionBoys' => 'Clothing, Shoes & Jewelry Boys',
                'FashionGirls' => 'Clothing, Shoes & Jewelry Girls',
                'FashionMen' => 'Clothing, Shoes & Jewelry Men',
                'FashionWomen' => 'Clothing, Shoes & Jewelry Women',
                'GardenAndOutdoor' => 'Garden & Outdoor',
                'GiftCards' => 'Gift Cards',
                'GroceryAndGourmetFood' => 'Grocery & Gourmet Food',
                'Handmade' => 'Handmade',
                'HealthPersonalCare' => 'Health, Household & Baby Care',
                'HomeAndKitchen' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Jewelry',
                'KindleStore' => 'Kindle Store',
                'LocalServices' => 'Home & Business Services',
                'Luggage' => 'Luggage & Travel Gear',
                'LuxuryBeauty' => 'Luxury Beauty',
                'Magazines  ' => 'Magazine Subscriptions',
                'MobileAndAccessories' => 'Cell Phones & Accessories',
                'MobileApps' => 'Apps & Gamess',
                'MoviesAndTV' => 'Movies & TV',
                'Music' => 'CDs & Vinyl',
                'MusicalInstruments' => 'Musical Instruments',
                'OfficeProducts' => 'Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Photo' => 'Camera & Photo',
                'Shoes' => 'Shoes',
                'Software' => 'Software',
                'SportsAndOutdoors' => 'Sports & Outdoors',
                'ToolsAndHomeImprovement' => 'Tools & Home Improvement',
                'ToysAndGames' => 'Toys & Games',
                'VHS' => 'VHS',
                'VideoGames' => 'Video Games',
                'Watches' => 'Watches',
            ),
        );
        return $cat;
    }
    /**
     * Add products search
     */
    function wca_add_products_search_count()
    {
        $count = get_option('wca_products_search_count');
        $count = $count + 1;
        update_option('wca_products_search_count', $count);
    }
    /**
     * WooCommerce plugin missing notices.
     */
    function ams_woocommerce_missing()
    {
        $massing_text = esc_html__('Affiliate Management System - WooCommerce Amazon requires WooCommerce to be installed and active. You can download', 'ams-wc-amazon');
        $translators_text = sprintf(
            '<div class="error"><p><strong>%s <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">%s</a> here.</strong></p></div>',
            esc_html($massing_text),
            esc_html__('WooCommerce', 'ams-wc-amazon')
        );
        echo wp_kses_post($translators_text);
    }
    /**
     * License not activation notices
     */
    function ams_plugin_license_active_massage()
    {
        $text = esc_html__('Affiliate Management System - WooCommerce Amazon plugin license not activated Please activate the plugin\'s license', 'ams-wc-amazon');
        $contain = sprintf('<div class="error"><p><strong>%s</strong></p></div>', esc_html($text));
        echo wp_kses_post($contain);
    }
    /**
     * License status check
     */
    function ams_plugin_license_status()
    {
        $status = get_option('ams_activated_status');
        if (strtolower($status) === strtolower('success')) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    if (!function_exists('ams_clean')) {
        function ams_clean($var)
        {
            if (is_array($var)) {
                return array_map('ams_clean', $var);
            } else {
                return is_scalar($var) ? sanitize_text_field($var) : $var;
            }
        }
    }
    /**
     * Create a product variation for a defined variable product ID.
     *
     * @since 3.0.0
     * @param int   $product_id | Post ID of the product parent variable product.
     * @param array $variation_data | The data to insert in the product.
     */
    function create_product_variation($product_id, $variation_data)
    {
        // Get the Variable product object (parent)
        $product = wc_get_product($product_id);
        $variation_post = array(
            'post_title' => $product->get_name(),
            'post_name' => 'product-' . $product_id . '-variation',
            'post_status' => 'publish',
            'post_parent' => $product_id,
            'post_type' => 'product_variation',
            'guid' => $product->get_permalink(),
        );
        // Creating the product variation
        $variation_id = wp_insert_post($variation_post);
        // Get an instance of the WC_Product_Variation object
        $variation = new WC_Product_Variation($variation_id);
        // Iterating through the variations attributes
        foreach ($variation_data['attributes'] as $attribute => $term_name) {
            $taxonomy = 'pa_' . $attribute; // The attribute taxonomy
            // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
            if (!taxonomy_exists($taxonomy)) {
                register_taxonomy(
                    $taxonomy,
                    'product_variation',
                    array(
                        'hierarchical' => false,
                        'label' => ucfirst($attribute),
                        'query_var' => true,
                        'rewrite' => array('slug' => sanitize_title($attribute)), // The base slug
                    )
                );
            }
            // Check if the Term name exist and if not we create it.
            if (!term_exists($term_name, $taxonomy)) {
                wp_insert_term($term_name, $taxonomy);
            }
            // Create the term
            $term_slug = get_term_by('name', $term_name, $taxonomy)->slug; // Get the term slug
            // Get the post Terms names from the parent variable product.
            $post_term_names = wp_get_post_terms($product_id, $taxonomy, array('fields' => 'names'));
            // Check if the post term exist and if not we set it in the parent variable product.
            if (!in_array($term_name, $post_term_names)) {
                wp_set_post_terms($product_id, $term_name, $taxonomy, true);
            }
            // Set/save the attribute data in the product variation
            update_post_meta($variation_id, 'attribute_' . $taxonomy, $term_slug);
        }
        ## Set/save all other data
        // SKU
        if (!empty($variation_data['sku'])) {
            $variation->set_sku($variation_data['sku']);
        }
        // Prices
        if (empty($variation_data['sale_price'])) {
            $variation->set_price($variation_data['regular_price']);
        } else {
            $variation->set_price($variation_data['sale_price']);
            $variation->set_sale_price($variation_data['sale_price']);
        }
        $variation->set_regular_price($variation_data['regular_price']);
        // Stock
        if (!empty($variation_data['stock_qty'])) {
            $variation->set_stock_quantity($variation_data['stock_qty']);
            $variation->set_manage_stock(true);
            $variation->set_stock_status('');
        } else {
            $variation->set_manage_stock(false);
        }
        $variation->set_weight(''); // weight (reseting)
        $variation->save(); // Save the data
    }

    /**
     * Multiple update cron window - NO API
     */
    add_action('wp_ajax_ams_product_availability', 'ams_product_availability');
    function ams_product_availability()
    {
        try {
            global $wpdb;

            // Check License
            if (ams_plugin_license_status() === false) {
                echo "<script>console.log('Plugin license not activated');</script>";
                $license = sprintf(esc_html__('Activate License!','ams-wc-amazon'));
                echo wp_kses_post($license);
                wp_die();
            }

            // If Added in CRON            
            $product_sku_cron = get_option('product_sku_cron', true);
            $product_tags_cron = get_option('product_tags_cron', true);
            $product_name_cron = get_option('product_name_cron', true);
            $product_price_cron = get_option('product_price_cron', true);
            $product_image_cron = get_option('product_image_cron', true);
            $product_review_cron = get_option('product_review_cron', true);
            $enable_amazon_review = get_option('enable_amazon_review', true);
            $product_variants_cron = get_option('product_variants_cron', true);
            $product_variant_image_cron = get_option('product_variant_image_cron', true);
            $product_category_cron = get_option('product_category_cron', true);
            $product_description_cron = get_option('product_description_cron', true);
            $product_out_of_stock_cron = get_option('product_out_of_stock_cron', true);

            if ( isset($_POST['data']) && $_POST['data'] != '' ) {
                $id_asin = $_POST['data'];
            } else {
                logImportVerification('The Cron update process started!',null);
                $asins = ams_get_all_products_info();
                $id_asin = array_combine($asins['id'], $asins['asin']);
                $products_url = [];
                foreach ($asins['product_id'] as $key => $value) {
                    $products_url[$key] = $value['url'];
                }
            }
            foreach ($id_asin as $id => $asin) {

                if (isset($_POST['product_url']) && !empty($_POST['product_url'])) {
                    $url = $_POST['product_url'];
                } else if (isset($products_url) && isset($products_url[$id]) && !empty($products_url[$id])) {
                    $url = $products_url[$id];
                } else {
                    $url = sprintf('https://www.amazon.%s/dp/%s', get_option('ams_amazon_country'), $asin);
                }

                logImportVerification('Product URL: ',$url);

                if (!class_exists('simple_html_dom')) {
                    require_once __DIR__ . '/Admin/lib/simplehtmldom/simple_html_dom.php';
                }
                $product_import = new \Amazon\Affiliate\Admin\ProductsSearchWithoutApi();

                // Get product data first time
                $product_url = sanitize_text_field($url);
                $user_agent = $product_import->user_agent();
                //echo '<pre>'; dd( $product_url ); echo '</pre>'; exit;

                $response_body = fetchAndValidateProductData($product_url, $user_agent);

                if (is_string($response_body) && strlen($response_body)) {

                    if (!class_exists('simple_html_dom')) {
                        require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
                    }

                    $html = new \simple_html_dom();
                    $html->load($response_body);
                    logImportVerification('Product update started...', null);
                    //echo '<pre>'; dd( 'Product update started...' ); echo '</pre>'; exit;

                    // Check for broken page
                    $message = check_for_broken_page($response_body, $html);
                    if ($message !== null) {
                        echo wp_kses_post($message);
                        logImportVerification($message, null);
                        wp_die();
                    }

                    // Get Parent ASIN from html
                    $parentSku = $product_import->getParentSkuFromHtml($html);
                    //echo 'here <pre>'; dd( $parentSku ); echo '</pre>'; exit;

                    if (!empty($parentSku)) {
                        logImportVerification('Valid parent SKU found: ', $parentSku);
                    } else {
                        logImportVerification('Failed to extract valid parent SKU', null);
                    }

                    // Check if product title exists, else abort
                    $productTitle = extractAmazonProductTitle($html);
                    if ($productTitle === false) {
                        logImportVerification('Product Title Extraction Failed', null);
                        wp_die();
                    }
                    $title = html_entity_decode($productTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    //echo '<pre>'; dd( $title ); echo '</pre>';

                    // Get Product Category
                    $product_category = $product_import->syncAndGetCategory($html);
                    //echo '<pre>'; dd( $product_category ); echo '</pre>';

                    // Get Product Content
                    $content = $product_import->fetchContentFromHtml($html);
                    //echo '<pre>'; dd( $content ); echo '</pre>';

                    // Get Product Short Description
                    $short_description = $product_import->fetchShortDescriptionFromHtml($html);
                    //echo '<pre>'; dd( $short_description ); echo '</pre>';

                    // Get Product Additional Content
                    $additional_description = $product_import->fetchAdditionalContentFromHtml($html);
                    //echo '<pre>'; dd( $additional_description ); echo '</pre>';

                    // Get Product gallery
                    $gallery = $product_import->fetchImagesFromHtml($html);
                    $image = array_shift($gallery);

                    $product = wc_get_product($id);
                    $post_id = $id;
                    //print_r('Data: ' . $post_id);

                    clean_completed_woocommerce_actions();
                    clean_all_actionscheduler_logs();

                    ////Start process before simple/variable

                    // Update Product SKU
                    if ($product_sku_cron) {
                        $asinElements = $html->find('#ASIN');
                        $asin = !empty($asinElements) ? $asinElements[0]->value : '';

                        if (empty($asin)) {
                            $elements = $html->find('input[name="ASIN.0"]');
                            $asin = !empty($elements) ? $elements[0]->value : '';
                        }

                        if (empty($asin)) {
                            $asin = $this->getSkuFromUrl($product_url);
                        }
                        update_post_meta($post_id, '_sku', $asin);
                    }

                    // Update Product title
                    if ($product_name_cron) {
                        $product_update = array(
                            'ID' => $post_id,
                            'post_title' => $title,
                            'post_name' => sanitize_title($title)
                        );
                        wp_update_post($product_update);
                        logImportVerification('Product Title Updated: ', $title);
                    }

                    // Update Product main content
                    if ($product_description_cron) {
                        if (!empty(trim($content))) {
                            $product_update = array(
                                'ID' => $post_id,
                                'post_content' => $content
                            );
                            wp_update_post($product_update);
                            logImportVerification('Product Content Updated!', null);
                        }
                    }

                    // Update Product Category
                    if ($product_category_cron) {
                        $product_category = $product_import->syncAndGetCategory($html);
                        if (!empty($product_category)) {
                            wp_set_object_terms($post_id, $product_category, 'product_cat');
                            logImportVerification('Product Category Updated: ', $product_category);
                        }
                    }

                    // Find the brand name
                    $brandElement = $html->find('a#bylineInfo', 0) 
                        ?: $html->find('div#bylineInfo_feature_div', 0) 
                        ?: $html->find('div#bondByLine_feature_div', 0);
                        
                    if ($brandElement) {
                        // Extract the raw brand text
                        $rawBrandName = trim($brandElement->plaintext);

                        // Clean up the brand name by removing unnecessary parts like "Visit the" and "Store"
                        $brandName = str_replace(array('Visit the', 'Store'), '', $rawBrandName);

                        // Trim any extra spaces
                        $brandName = trim($brandName);

                        logImportVerification('Brand: ' . $brandName);

                        // Check if the brand exists in the 'product_brand' taxonomy
                        $brandTerm = term_exists($brandName, 'product_brand');

                        if (!$brandTerm) {
                            // If the brand doesn't exist, create it
                            $brandTerm = wp_insert_term($brandName, 'product_brand');
                        }

                        if (is_wp_error($brandTerm)) {
                            logImportVerification('Error creating brand term: ' . $brandTerm->get_error_message());
                        } else {
                            // Get the term ID
                            $brandTermId = isset($brandTerm['term_id']) ? $brandTerm['term_id'] : $brandTerm;

                            // Assuming the product is represented by post_id (WooCommerce product post ID)
                            $productId = $post_id;  // Replace with your actual product post_id variable

                            // Assign the brand to the product
                            wp_set_object_terms($productId, intval($brandTermId), 'product_brand');

                            // Optional: You can also save the brand as custom meta if needed
                            update_post_meta($productId, '_product_brand', $brandName);

                            logImportVerification('Brand assigned to product successfully.');
                        }
                    } else {
                        logImportVerification('Brand not found in the provided HTML.');
                    }
                    // Find the brand name


                    // Update the GTIN, UPC, EAN, or ISBN code
                    $upcElement = $html->find('div#productDetails_expanderTables_depthLeftSections', 0);

                    if ($upcElement) {
                        $upcCode = ''; // Initialize variable

                        // Iterate through table rows to find GTIN, UPC, EAN, or ISBN
                        foreach ($upcElement->find('table.prodDetTable tr') as $row) {
                            $header = $row->find('th', 0); // Get the header cell
                            $value = $row->find('td', 0); // Get the value cell

                            if ($header && $value) {
                                $headerText = trim($header->plaintext);
                                $valueText = trim($value->plaintext);

                                // Check for GTIN, UPC, EAN, or ISBN
                                if (stripos($headerText, 'UPC') !== false || stripos($headerText, 'GTIN') !== false || stripos($headerText, 'EAN') !== false || stripos($headerText, 'ISBN') !== false) {
                                    $upcCode = $valueText; // Extract the value
                                    break; // Exit loop once found
                                }
                            }
                        }

                        if (!empty($upcCode)) {
                            // Save the value to the default WooCommerce GTIN/UPC/EAN/ISBN fields
                            update_post_meta($post_id, '_gtin', $upcCode); // GTIN field
                            update_post_meta($post_id, '_upc', $upcCode);  // UPC field
                            update_post_meta($post_id, '_ean', $upcCode);  // EAN field
                            update_post_meta($post_id, '_isbn', $upcCode); // ISBN field
                        }
                    }
                    // Update the GTIN, UPC, EAN, or ISBN code
            

                    // Check remote amazon images.
                    if($product_image_cron) {
                        // Set product feature image.
                        $gallery = $product_import->fetchImagesFromHtml($html);

                        $image = array_shift($gallery);
                        if( $image ) {
                            // Remove featured image and url.
                            delete_product_images($post_id);
                            reset_product_thumbnail_url($post_id, $flag=0);
                        }
                        
                        if( count($gallery) > 0 ) {
                            // Remove product gallery images and url.
                            delete_product_gallery_images($post_id);
                            reset_product_thumbnail_url($post_id, $flag=1);
                        }
                        
                        if ( 'Yes' === get_option( 'ams_remote_amazon_images' ) ) {
                            // Set featured image url
                            if( $image ) {
                                attach_product_thumbnail_url( $post_id, $image, 0 );
                            }
                            // Set featured image gallary
                            if( count($gallery) > 0 ) {
                                attach_product_thumbnail_url( $post_id, $gallery, 1 );
                            }
                        } else {
                            // Set featured image url
                            if( $image ) {
                                attach_product_thumbnail($post_id, $image, 0);
                            }
                            // Set featured image gallary
                            if( count($gallery) > 0 ) {
                                foreach( $gallery as $image ) {
                                    // Set gallery image.
                                    attach_product_thumbnail( $post_id, $image, 1 );
                                }
                            }
                        }
                    }


                    // Get Product attributes
                    $attributes = $product_import->getProductAttributeFromHtml($html);
                    //echo '<pre>'; dd( $attributes ); echo '</pre>';


                    //Run if variable
                    if (count($attributes) > 0) {
                        $product = wc_get_product($post_id);
                        // print_r('Data: ' . $post_id); exit;

                        if($parentSku) {
                            update_post_meta($post_id, '_sku', $parentSku);
                        }
                        //echo '<pre>'; dd( $parentSku ); echo '</pre>';

                        // Delete product short description
                        $postData = array(
                            'ID' => $post_id,
                            'post_excerpt' => ''
                        );
                        wp_update_post($postData);

                        // Update Additional Description
                        if (!empty($additional_description)) {
                            update_post_meta($post_id, '_ams_additional_information', $additional_description);
                            logImportVerification('Additional description updated.', null);
                        }

                        $skus = $imported_skus = $product_variations = [];

                        // Get all variants based on the SKUs found
                        $all_skus = $product_import->getSkusFromHtml($html);
                        //echo '<pre>'; print_r($all_skus); echo '</pre>'; exit;

                        $variation_ids = $product_import->getProductFirstVariationFromHtml($html, $parentSku, $product_url, $all_skus);
                        //echo '<pre>'; print_r($variation_ids); echo '</pre>'; exit;

                        // variations to process
                        $variation_limit = get_option('ams_variation_limit', 5);

                        // Update Variants
                        if ($product_variants_cron && count($attributes) > 0) {

                            // Check if there are variation IDs:
                            if(!empty($variation_ids) && count($variation_ids) > 0) {

                                // Apply the dynamic variations to process
                                $variation_ids = array_slice($variation_ids, 0, $variation_limit);

                                // Determine the preferred URL-generation function based solely on product title extraction.
                                $preferred_function = null;
                                $first_variation_processed = false;

                                foreach ($variation_ids as $variation_id) {
                                    if (in_array($variation_id, $imported_skus)) {
                                        continue;
                                    }
                                    array_push($imported_skus, $variation_id);

                                    // For the first variation, decide which function to use based solely on product title.
                                    if (!$first_variation_processed) {
                                        // Try using function 1 with regular curl first
                                        $test_url = generate_amazon_url_1($product_url, $variation_id);
                                        $userAgent = getAlternatingBool();
                                        $test_content = $product_import->getContentUsingCurl($test_url, $userAgent);
                                        $test_html = new \simple_html_dom();
                                        $test_html->load($test_content);
                                        
                                        // If regular curl fails or no attributes found, try scraping
                                        if (!$test_content || count($product_import->getProductAttributeFromHtml($test_html)) == 0) {
                                            $test_content = executeScrapingService($test_url, true);
                                            $test_html = new \simple_html_dom();
                                            $test_html->load($test_content);
                                        }
                                        
                                        // Check if product title exists; if not, choose function 2
                                        $productTitle = extractAmazonProductTitle($test_html);
                                        if ($productTitle === false) {
                                            $preferred_function = 2;
                                            //echo "<pre>Preferred function set to 2 (function 1 failed to extract product title).</pre>";
                                            logImportVerification('function 1 failed to extract product title');
                                        } else {
                                            $title = html_entity_decode($productTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            $preferred_function = 1;
                                            //echo "<pre>Preferred function set to 1 based on product title: {$title}</pre>";
                                            logImportVerification('Preferred function set to 1 based on product title: {$title}');
                                        }
                                        $test_html->clear();
                                        $first_variation_processed = true;
                                    }

                                    // Use the preferred function to generate the base URL.
                                    if ($preferred_function === 1) {
                                        $base_url = generate_amazon_url_1($product_url, $variation_id);
                                    } else {
                                        $base_url = generate_amazon_url_2($product_url, $variation_id);
                                    }
                                    
                                    //echo "<pre>Processing Variation ID: {$variation_id} using function {$preferred_function}. Base URL: {$base_url}</pre>";

                                    // First try with regular curl
                                    $userAgent = getAlternatingBool();
                                    $content = $product_import->getContentUsingCurl($base_url, $userAgent);
                                    $loop_html = new \simple_html_dom();
                                    $loop_html->load($content);
                                    
                                    // Check if we need to use scraping service
                                    if (!$content || count($product_import->getProductAttributeFromHtml($loop_html)) == 0) {
                                        $content = executeScrapingService($base_url, true);
                                        $loop_html = new \simple_html_dom();
                                        $loop_html->load($content);
                                    }

                                    $productPrice = $product_import->fetchPriceFromHtml($loop_html);

                                    // If no ppd div found, retry twice with proxy
                                    $retry = 2;
                                    while (isset($productPrice['search_area']) && $productPrice['search_area'] == 'entire HTML' && $retry > 0) {
                                        $content = executeScrapingService($base_url, true);
                                        $loop_html = new \simple_html_dom();
                                        $loop_html->load($content);
                                        $productPrice = $product_import->fetchPriceFromHtml($loop_html);
                                        $retry--;
                                    }

                                    $regular_price = isset($productPrice['final_prices']['regular_price'])
                                        ? $productPrice['final_prices']['regular_price']
                                        : (isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0);
                                    $sale_price = isset($productPrice['final_prices']['sale_price'])
                                        ? $productPrice['final_prices']['sale_price']
                                        : (isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0);

                                    // Add ScraperAPI retry if prices are zero
                                    $isUpdate = true;
                                    if ($regular_price == 0) {
                                        $scraperapi = get_scraping_services_config()['scraperapi'];
                                        if (!empty($scraperapi['api_key']) && ($isUpdate ? $scraperapi['on_update'] : $scraperapi['is_active'])) {
                                            $content = call_user_func($scraperapi['execute'], $base_url, $scraperapi['api_key']);
                                            if ($content) {
                                                $loop_html = new \simple_html_dom();
                                                $loop_html->load($content);
                                                $productPrice = $product_import->fetchPriceFromHtml($loop_html);
                                                
                                                // Update prices with new values
                                                $regular_price = isset($productPrice['final_prices']['regular_price'])
                                                    ? $productPrice['final_prices']['regular_price']
                                                    : (isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0);
                                                $sale_price = isset($productPrice['final_prices']['sale_price'])
                                                    ? $productPrice['final_prices']['sale_price']
                                                    : (isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0);
                                            }
                                        } else {
                                            //echo '<pre>Warning: ScraperAPI service is disabled. Enable it for better price scraping.</pre>';
                                            logImportVerification('Warning: ScraperAPI service is disabled. Enable it for better price scraping.');
                                        }
                                    }

                                    $currency = $product_import->fetchCurrencyFromHtml($loop_html);
                                    logImportVerification('Currency: ', $currency);

                                    if ($regular_price > 0 || $sale_price > 0) {
                                        $product_status = 'instock';
                                    } else {
                                        $product_status = check_product_stock_status($loop_html);
                                        if ($product_status === 'instock') {
                                            $product_status = 'outofstock';
                                            logImportVerification('Status changed to outofstock due to zero prices');
                                        }
                                    }
                                    logImportVerification('Final stock status: ', $product_status);

                                    $quantity = 0;
                                    if ($qty = $loop_html->find('#availability span', 0)) {
                                        $quantity = $product_import->parseNumberFromString($qty->text());
                                    }
                                    logImportVerification('Quantity: ', $quantity);

                                    $short_description = $product_import->fetchVariationContentFromHtml($loop_html);
                                    $additional_description = $product_import->fetchAdditionalContentFromHtml($loop_html);

                                    if ($product_variant_image_cron) {
                                        $v_gallery = $product_import->fetchImagesFromHtml($loop_html);
                                        $image_limit = get_option('ams_variation_image_limit', 5);
                                        if ($image_limit > 0) {
                                            $v_gallery = array_slice($v_gallery, 0, $image_limit);
                                        }
                                    }

                                    $attributes = $product_import->getProductAttributeFromHtml($loop_html);

                                    $product_variations[] = array(
                                        'sku'                     => $variation_id,
                                        'stock_qty'               => $quantity,
                                        'stock_status'            => $product_status,
                                        'regular_price'           => $regular_price,
                                        'sale_price'              => $sale_price,
                                        'attributes'              => $attributes,
                                        'description'             => $short_description,
                                        'product_image_gallery'   => isset($v_gallery) ? $v_gallery : array(),
                                        'additional_description'  => $additional_description,
                                    );
                                }

                            }

                            //echo '<pre>'; dd( $product_variations ); echo '</pre>';
                            if (count($product_variations) > 0) {
                                wc_update_product_variations($post_id, $product_variations,$parentSku);
                            }

                            logImportVerification('Variable product updated!', null);
                        }
                        
                        // Update product sku
                        if ($product_sku_cron) {
                            $parentSkuResult = $product_import->getParentSkuFromHtml($html);
                            
                            if (is_string($parentSkuResult) && !empty($parentSkuResult)) {
                                // If it's a string, use it directly
                                $parentSku = $parentSkuResult;
                            } elseif (is_array($parentSkuResult) && count($parentSkuResult) > 0) {
                                // If it's an array, process it as before
                                $parentSkuStr = array_shift($parentSkuResult);
                                $strExp = explode(":", $parentSkuStr);
                                $parentSku = str_replace("\"", "", str_replace(",", "", trim(end($strExp))));
                            } else {
                                $parentSku = null;
                            }

                            if ($parentSku) {
                                update_post_meta($post_id, '_sku', $parentSku);
                            }
                        }
                    }

                    //Run if simple
                    else {
                        $product = wc_get_product($post_id);
                        //print_r('After-simple: ' . $post_id);

                        // Update Additional Description
                        if ($product_description_cron) {
                            $short_description = $product_import->fetchShortDescriptionFromHtml($html);
                            if (!empty($short_description)) {
                                $product->set_short_description($short_description);
                                $product->save(); // Save the product
                            }

                            $additional_description = $product_import->fetchAdditionalContentFromHtml($html);
                            if (!empty($additional_description)) {
                                update_post_meta($post_id, '_ams_additional_information', $additional_description);
                            }
                        }

                        // Product price and stock status
                        $product_status = 'instock';
                        $quantity = 0;

                        if ($product_price_cron || $product_out_of_stock_cron) {
                            // Fetch product price from HTML
                            $productPrice = $product_import->fetchPriceFromHtml($html);
                            $regular_price = isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0;
                            $sale_price = isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0;
                            logImportVerification('Regular price: ', $regular_price);
                            logImportVerification('Sale price: ', $sale_price);
                            //error_log("Product Price: " . print_r($productPrice, true));

                            // Get removal setting
                            $remove_unavailable_products = get_option('ams_remove_unavailable_products') === 'Yes';
                            
                            // Check if both prices are 0
                            if ($regular_price == 0 && $sale_price == 0) {
                                // Check if removal setting is enabled
                                if ($remove_unavailable_products) {
                                    // Refresh product object
                                    $_product = wc_get_product($post_id);
                                    
                                    // Delete the product if it exists
                                    if ($_product) {
                                        $_product->delete(true);
                                        logImportVerification("Product $post_id has been deleted due to zero prices.");
                                        exit("Product removed due to zero prices.");
                                    }
                                }
                            }

                            // Fetch currency from HTML
                            $currency = $product_import->fetchCurrencyFromHtml($html);
                            logImportVerification('Currency: ', $currency);

                            // Determine initial stock status based on price availability
                            if ($regular_price > 0 || $sale_price > 0) {
                                $product_status = 'instock';
                            } else {
                                $product_status = 'outofstock';
                            }

                            // Additional check for out of stock status
                            if ($html->find('#outOfStock .a-color-price', 0)) {
                                $product_status = 'outofstock';
                            }

                            logImportVerification('Initial Stock status: ', $product_status);

                            if ($product_out_of_stock_cron) {
                                if ($product_status == 'outofstock' && 'Yes' == get_option('ams_remove_unavailable_products')) {
                                    $product_import->removeProductIfNotExists($post_id);
                                    logImportVerification('Product removal: ', "Product with ID $post_id has been processed for removal as it is out of stock.");
                                    wp_die(
                                        esc_html__('OutOfStock!', 'ams-wc-amazon'),
                                        ['response' => 200]
                                    );
                                }

                                if ($availability = $html->find('#availability span', 0)) {
                                    $quantity = $product_import->parseNumberFromString($availability->text());
                                }

                                if ($quantity > 0) {
                                    update_post_meta($post_id, '_stock', $quantity);
                                    update_post_meta($post_id, '_manage_stock', 'yes');
                                } else {
                                    update_post_meta($post_id, '_stock', '');
                                    update_post_meta($post_id, '_manage_stock', 'no');
                                }
                                update_post_meta($post_id, '_stock_status', $product_status);
                            }

                            if ($product_price_cron) {
                                // Update regular price
                                update_post_meta($post_id, '_regular_price', $regular_price);
                                $product->set_regular_price($regular_price);
                                
                                // Update sale price and current price
                                if ($sale_price > 0 && $sale_price < $regular_price) {
                                    update_post_meta($post_id, '_sale_price', $sale_price);
                                    update_post_meta($post_id, '_price', $sale_price);
                                    $product->set_price($sale_price);
                                    $product->set_sale_price($sale_price);
                                } else {
                                    delete_post_meta($post_id, '_sale_price');
                                    update_post_meta($post_id, '_price', $regular_price);
                                    $product->set_price($regular_price);
                                    $product->set_sale_price('');
                                }
                                
                                // Update currency
                                if ($currency) {
                                    update_post_meta($post_id, '_product_currency', $currency);
                                }
                            }
                        }

                        // Product images + feature image
                        if ($product_image_cron) {
                            // Get the image limit from plugin settings
                            $image_limit = get_option('ams_variation_image_limit', 5);
                            
                            // Set product feature image.
                            $gallery = $product_import->fetchImagesFromHtml($html);
                            
                            // Apply the limit to the gallery
                            $gallery = array_slice($gallery, 0, $image_limit);
                            
                            $image = array_shift($gallery);
                            $use_remote_images = ('Yes' === get_option('ams_remote_amazon_images'));
                            
                            // Always remove existing images and URLs
                            if ($image) {
                                // Remove featured image and url.
                                delete_product_images($post_id);
                                reset_product_thumbnail_url($post_id, $flag = 0);
                            }
                            
                            if (count($gallery) > 0) {
                                // Remove product gallery images and url.
                                delete_product_gallery_images($post_id);
                                reset_product_thumbnail_url($post_id, $flag = 1);
                            }
                            
                            if ($use_remote_images) {
                                // Set featured image url
                                if ($image) {
                                    attach_product_thumbnail_url($post_id, $image, 0);
                                }
                                // Set featured image gallery
                                if (count($gallery) > 0) {
                                    attach_product_thumbnail_url($post_id, $gallery, 1);
                                }
                                // Remove any locally stored images
                                delete_local_product_images($post_id);
                            } else {
                                // Set featured image
                                if ($image) {
                                    attach_product_thumbnail($post_id, $image, 0);
                                }
                                // Set featured image gallery
                                foreach ($gallery as $gallery_image) {
                                    // Set gallery image.
                                    attach_product_thumbnail($post_id, $gallery_image, 1);
                                }
                                // Remove any stored image URLs
                                delete_product_image_urls($post_id);
                            }
                        }

                        $product->save(); // Save all changes
                        logImportVerification('Simple product updated!', null);
                    }

                    // Update Product Review
                    if ($enable_amazon_review && $product_review_cron) {
                        // Get review limit from settings
                        $review_limit = get_option('multiple_import_review_limit', 10);
                        
                        // Scrape the reviews
                        $reviews = scrape_amazon_reviews($html, $review_limit);
                        
                        logImportVerification("Processed " . count($reviews) . " reviews");

                        if (!empty($reviews) && isset($post_id)) {
                            // Get existing reviews
                            $existing_reviews = get_comments([
                                'post_id' => $post_id,
                                'type' => 'review',
                                'status' => 'approve'
                            ]);
                            
                            // Create array of existing review hashes
                            $existing_hashes = [];
                            foreach ($existing_reviews as $existing_review) {
                                $existing_hash = get_comment_meta($existing_review->comment_ID, 'review_hash', true);
                                if (!empty($existing_hash)) {
                                    $existing_hashes[$existing_hash] = $existing_review->comment_ID;
                                }
                            }

                            // Initialize rating totals
                            $rating_sum = 0;
                            $rating_count = 0;

                            // Process each review
                            foreach ($reviews as $review_hash => $review) {
                                // Skip if review already exists
                                if (isset($existing_hashes[$review_hash])) {
                                    logImportVerification("Skipping duplicate review: " . $review['title']);
                                    continue;
                                }

                                // Prepare comment data
                                $commentdata = [
                                    'comment_post_ID' => $post_id,
                                    'comment_author' => $review['reviewer_name'],
                                    'comment_content' => $review['text'],
                                    'comment_date' => $review['date'],
                                    'comment_date_gmt' => get_gmt_from_date($review['date']),
                                    'comment_approved' => 1,
                                    'comment_type' => 'review',
                                    'user_id' => 0
                                ];

                                // Insert the comment
                                $comment_id = wp_insert_comment($commentdata);

                                if ($comment_id) {
                                    // Add all the comment meta
                                    add_comment_meta($comment_id, 'rating', $review['rating']);
                                    add_comment_meta($comment_id, 'review_hash', $review_hash);
                                    add_comment_meta($comment_id, 'verified', 1);
                                    add_comment_meta($comment_id, 'title', $review['title']);

                                    if (!empty($review['reviewer_image'])) {
                                        add_comment_meta($comment_id, 'reviewer_image', $review['reviewer_image']);
                                    }

                                    $rating_sum += floatval($review['rating']);
                                    $rating_count++;

                                    logImportVerification("Added review: " . $review['title'] . " with ID: " . $comment_id);
                                }
                            }

                            // Update product rating if we added any new reviews
                            if ($rating_count > 0) {
                                $product = wc_get_product($post_id);
                                if ($product) {
                                    // Get actual count of approved reviews
                                    $actual_review_count = get_comments([
                                        'post_id' => $post_id,
                                        'type' => 'review',
                                        'status' => 'approve',
                                        'count' => true
                                    ]);

                                    // Calculate actual rating sum
                                    $actual_rating_sum = 0;
                                    $product_reviews = get_comments([
                                        'post_id' => $post_id,
                                        'type' => 'review',
                                        'status' => 'approve'
                                    ]);

                                    foreach ($product_reviews as $review) {
                                        $rating = get_comment_meta($review->comment_ID, 'rating', true);
                                        $actual_rating_sum += floatval($rating);
                                    }

                                    // Calculate new average
                                    $new_average = $actual_rating_sum / $actual_review_count;

                                    // Update all rating meta
                                    update_post_meta($post_id, '_wc_average_rating', round($new_average, 2));
                                    update_post_meta($post_id, '_wc_rating_count', $actual_review_count);
                                    update_post_meta($post_id, '_wc_review_count', $actual_review_count);
                                    update_post_meta($post_id, '_wc_rating_sum', $actual_rating_sum);

                                    // Clear all relevant caches
                                    delete_transient('wc_product_reviews_' . $post_id);
                                    delete_transient('wc_average_rating_' . $post_id);
                                    wp_cache_delete($post_id, 'product');
                                    
                                    if (function_exists('wc_delete_product_transients')) {
                                        wc_delete_product_transients($post_id);
                                    }

                                    logImportVerification("Updated product rating. New average: " . round($new_average, 2));
                                }
                            }

                            logImportVerification("Completed review import. Added " . $rating_count . " new reviews");
                        }
                    }
                    // Update Product Review


                    update_post_meta($post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
                    update_post_meta($post_id, 'ams_last_cron_status',1);
                    update_post_meta($post_id, '_ams_product_url', $url);

                    logImportVerification('Product Updated!',null);
                    echo esc_html__('Product updated Successfully!', 'ams-wc-amazon') . '<a href="' . esc_url($url) . '" target="_blank" style="color: white;">' . esc_html(mb_strimwidth($url, 0, 35, '...')) . '</a>';
                    wc_delete_product_transients($post_id);
                    
                    clean_completed_woocommerce_actions();
                    clean_all_actionscheduler_logs();

                } else {
                    error_log("Failed to fetch product data");
                    echo 'Failed to fetch product data';
                }
            }
        }   catch (\Throwable $th) {
            // Check if post_id exists in $_POST
            if (isset($_POST['post_id'])) {
                update_post_meta($_POST['post_id'], 'ams_last_cron_update', date('Y-m-d H:i:s'));
                update_post_meta($_POST['post_id'], 'ams_last_cron_status', 1);
            } else if (isset($id) && !empty($id)) {
                // Use the $id variable from the foreach loop if it's available
                update_post_meta($id, 'ams_last_cron_update', date('Y-m-d H:i:s'));
                update_post_meta($id, 'ams_last_cron_status', 1);
            }
            // Optionally log the error
            error_log("Exception in ams_product_availability: " . $th->getMessage());
        }
        //error_log("======== CRON ENDED =======");
        wp_die();
    }


    add_action( 'manage_product_posts_custom_column', 'ExtraCronColumn', 10, 2 );
    function ExtraCronColumn( $column, $postid ) {

        if( $column == 'import_method' ) {
            $import_method = '';
            switch(get_post_meta($postid, '_import_method', true)) {
                case '1':
                    $import_method = 'AMAZON API';
                    break;
                case '2':
                    $import_method = 'NO API - BY SEARCH';
                    break;
                case '3':
                    $import_method = 'NO API - BY URLS';
                    break;
            }
            echo $import_method;
        }
        
        if ($column == 'cron_run_status') {
            $date = get_post_meta( $postid, 'ams_last_cron_update', true);
            $product_url = get_post_meta( $postid, '_ams_product_url', true);
            
            $import_method = '';
            switch(get_post_meta($postid, '_import_method', true)) {
                case '1':
                    $import_method = 'AMAZON API';
                    break;
                case '2':
                    $import_method = 'NO API - BY SEARCH';
                    break;
                case '3':
                    $import_method = 'NO API - BY URLS';
                    break;
            }
            
            $status = get_post_meta($postid, 'ams_last_cron_status', true);
            $data_attr = 'data-post-id="' . $postid . '" data-url="' . $product_url . '"';

            if (!empty($date)) {
                $status_text = $status == 1 ? 'Updated' : 'Not Updated';
                $html = '<div class="cron-status-container status-' . $status . '">';
                $html .= '<div class="cron-details">';
                $html .= '<span class="cron-status-text">' . $status_text . '</span>';
                $html .= '<span class="cron-date">' . date('d/m/Y \a\t h:i a', strtotime($date)) . '</span>';
                $html .= '<span class="cron-import-method">' . $import_method . '</span>';
                $html .= '</div>';
                $html .= '<button type="button" class="button wca-product-update-request" ' . $data_attr . '>Run Cron</button>';
                $html .= '</div>';
                echo $html;
            } elseif ($product_url) {
                $status_text = 'Not Started';
                $html = '<div class="cron-status-container status-' . $status . '">';
                $html .= '<div class="cron-details">';
                $html .= '<span class="cron-status-text">' . $status_text . '</span>';
                $html .= '<span class="cron-import-method">' . $import_method . '</span>';
                $html .= '</div>';
                $html .= '<button type="button" class="button wca-product-update-request" ' . $data_attr . '>Run Cron</button>';
                $html .= '</div>';
                echo $html;
            } else {
                echo '<span class="na">–</span>';
            }
        }

        if ($column == 'custom_price') {
            $product = wc_get_product($postid);        
            if (!get_post_meta($product->get_id(), '_ams_product_url', true)) {
                echo $product->get_price_html();
            } else {
                if ($product->is_type('variable')) {
                    $priceArray = [];
                    $variations = $product->get_children();
                    if (count($variations) > 0) {
                        foreach ($variations as $variation_id) {
                            $w_product = wc_get_product($variation_id);
                            if ($w_product->is_on_sale()) {
                                $sale_price = $w_product->get_sale_price();
                                $priceArray[] = [
                                    'ID' => $w_product->get_id(),
                                    'price' => $sale_price,
                                ];
                            } else {
                                $regular_price = $w_product->get_regular_price();
                                $priceArray[] = [
                                    'ID' => $w_product->get_id(),
                                    'price' => $regular_price,
                                ];
                            }
                        }

                        $prices = array_column($priceArray, 'price');
                        $minPrice = $product->get_variation_price('min');
                        $maxPrice = $product->get_variation_price('max');

                        $min = wc_price($minPrice);
                        $max = wc_price($maxPrice);

                        if ($minPrice == $maxPrice) {
                            echo $min;
                        } else {
                            if ($minPrice && $maxPrice) {
                                echo $min . ' - ' . $max;
                            } elseif ($minPrice) {
                                echo $min;
                            } else {
                                echo $max;
                            }
                        }
                    }
                } else {
                    if ($product->is_on_sale()) {
                        $regular_price = $product->get_regular_price();
                        $sale_price = $product->get_sale_price();
                        echo '<del>' . wc_price($regular_price) . '</del> <ins>' . wc_price($sale_price) . '</ins>';
                    } else {
                        $regular_price = $product->get_regular_price();
                        echo wc_price($regular_price);
                    }
                }
            }
        }
    }

    // make header of column clickable for sort
    function ams_sortable_product_columns_header( $sortable_columns ) {
        $sortable_columns['custom_price'] = 'price';
        return $sortable_columns;
    }

    function checkExistProduct($sku)
    {
        $the_query = new WP_Query($args);
        // The Loop
        $return = false;
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $post_id = get_the_ID();
                $_sku = get_post_meta($post_id, "_sku", true);
                if ($_sku == $sku) {
                    $return = true;
                    break;
                }
            }
        }
        /* Restore original Post Data */
        wp_reset_postdata();
        return $return;
    }

    add_action('wp_ajax_ams_get_currency_by_country', 'ams_get_currency_by_country');
    function ams_get_currency_by_country() {
        $data = ams_woocommerce_currency_countries();
        $selectedCountry = $_POST['country'];
        $result = ['status' => false];
        if($selectedCountry == 'com') {
            $result = ['status' => true, 'value' => 'USD'];
        } else {
            foreach ($data as $key => $value) {
                if(in_array(strtoupper($selectedCountry), $value)) {
                    $result = ['status' => true, 'value' => $key];
                    break;
                }
            }
        }
        echo json_encode($result);
        wp_die();    
    }

    function ams_woocommerce_currency_countries() {
        return array(
            'AFN' => array( 'AF' ),
            'ALL' => array( 'AL' ),
            'DZD' => array( 'DZ' ),
            'USD' => array( 'AS', 'IO', 'GU', 'MH', 'FM', 'MP', 'PW', 'PR', 'TC', 'US', 'UM', 'VI', 'US'),
            'EUR' => array( 'AD', 'AT', 'BE', 'CY', 'EE', 'FI', 'FR', 'GF', 'TF', 'DE', 'GR', 'GP', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'MQ', 'YT', 'MC', 'ME', 'NL', 'PT', 'RE', 'PM', 'SM', 'SK', 'SI', 'ES' ),
            'AOA' => array( 'AO' ),
            'XCD' => array( 'AI', 'AQ', 'AG', 'DM', 'GD', 'MS', 'KN', 'LC', 'VC' ),
            'ARS' => array( 'AR' ),
            'AMD' => array( 'AM' ),
            'AWG' => array( 'AW' ),
            'AUD' => array( 'AU', 'CX', 'CC', 'HM', 'KI', 'NR', 'NF', 'TV' ),
            'AZN' => array( 'AZ' ),
            'BSD' => array( 'BS' ),
            'BHD' => array( 'BH' ),
            'BDT' => array( 'BD' ),
            'BBD' => array( 'BB' ),
            'BYR' => array( 'BY' ),
            'BZD' => array( 'BZ' ),
            'XOF' => array( 'BJ', 'BF', 'ML', 'NE', 'SN', 'TG' ),
            'BMD' => array( 'BM' ),
            'BTN' => array( 'BT' ),
            'BOB' => array( 'BO' ),
            'BAM' => array( 'BA' ),
            'BWP' => array( 'BW' ),
            'NOK' => array( 'BV', 'NO', 'SJ' ),
            'BRL' => array( 'BR' ),
            'BND' => array( 'BN' ),
            'BGN' => array( 'BG' ),
            'BIF' => array( 'BI' ),
            'KHR' => array( 'KH' ),
            'XAF' => array( 'CM', 'CF', 'TD', 'CG', 'GQ', 'GA' ),
            'CAD' => array( 'CA' ),
            'CVE' => array( 'CV' ),
            'KYD' => array( 'KY' ),
            'CLP' => array( 'CL' ),
            'CNY' => array( 'CN' ),
            'HKD' => array( 'HK' ),
            'COP' => array( 'CO' ),
            'KMF' => array( 'KM' ),
            'CDF' => array( 'CD' ),
            'NZD' => array( 'CK', 'NZ', 'NU', 'PN', 'TK' ),
            'CRC' => array( 'CR' ),
            'HRK' => array( 'HR' ),
            'CUP' => array( 'CU' ),
            'CZK' => array( 'CZ' ),
            'DKK' => array( 'DK', 'FO', 'GL' ),
            'DJF' => array( 'DJ' ),
            'DOP' => array( 'DO' ),
            'ECS' => array( 'EC' ),
            'EGP' => array( 'EG' ),
            'SVC' => array( 'SV' ),
            'ERN' => array( 'ER' ),
            'ETB' => array( 'ET' ),
            'FKP' => array( 'FK' ),
            'FJD' => array( 'FJ' ),
            'GMD' => array( 'GM' ),
            'GEL' => array( 'GE' ),
            'GHS' => array( 'GH' ),
            'GIP' => array( 'GI' ),
            'QTQ' => array( 'GT' ),
            'GGP' => array( 'GG' ),
            'GNF' => array( 'GN' ),
            'GWP' => array( 'GW' ),
            'GYD' => array( 'GY' ),
            'HTG' => array( 'HT' ),
            'HNL' => array( 'HN' ),
            'HUF' => array( 'HU' ),
            'ISK' => array( 'IS' ),
            'INR' => array( 'IN' ),
            'IDR' => array( 'ID' ),
            'IRR' => array( 'IR' ),
            'IQD' => array( 'IQ' ),
            'GBP' => array( 'IM', 'JE', 'GS', 'GB', 'UK' ),
            'ILS' => array( 'IL' ),
            'JMD' => array( 'JM' ),
            'JPY' => array( 'JP' ),
            'JOD' => array( 'JO' ),
            'KZT' => array( 'KZ' ),
            'KES' => array( 'KE' ),
            'KPW' => array( 'KP' ),
            'KRW' => array( 'KR' ),
            'KWD' => array( 'KW' ),
            'KGS' => array( 'KG' ),
            'LAK' => array( 'LA' ),
            'LBP' => array( 'LB' ),
            'LSL' => array( 'LS' ),
            'LRD' => array( 'LR' ),
            'LYD' => array( 'LY' ),
            'CHF' => array( 'LI', 'CH' ),
            'MKD' => array( 'MK' ),
            'MGF' => array( 'MG' ),
            'MWK' => array( 'MW' ),
            'MYR' => array( 'MY' ),
            'MVR' => array( 'MV' ),
            'MRO' => array( 'MR' ),
            'MUR' => array( 'MU' ),
            'MXN' => array( 'MX' ),
            'MDL' => array( 'MD' ),
            'MNT' => array( 'MN' ),
            'MAD' => array( 'MA', 'EH' ),
            'MZN' => array( 'MZ' ),
            'MMK' => array( 'MM' ),
            'NAD' => array( 'NA' ),
            'NPR' => array( 'NP' ),
            'ANG' => array( 'AN' ),
            'XPF' => array( 'NC', 'WF' ),
            'NIO' => array( 'NI' ),
            'NGN' => array( 'NG' ),
            'OMR' => array( 'OM' ),
            'PKR' => array( 'PK' ),
            'PAB' => array( 'PA' ),
            'PGK' => array( 'PG' ),
            'PYG' => array( 'PY' ),
            'PEN' => array( 'PE' ),
            'PHP' => array( 'PH' ),
            'PLN' => array( 'PL' ),
            'QAR' => array( 'QA' ),
            'RON' => array( 'RO' ),
            'RUB' => array( 'RU' ),
            'RWF' => array( 'RW' ),
            'SHP' => array( 'SH' ),
            'WST' => array( 'WS' ),
            'STD' => array( 'ST' ),
            'SAR' => array( 'SA' ),
            'RSD' => array( 'RS' ),
            'SCR' => array( 'SC' ),
            'SLL' => array( 'SL' ),
            'SGD' => array( 'SG' ),
            'SBD' => array( 'SB' ),
            'SOS' => array( 'SO' ),
            'ZAR' => array( 'ZA' ),
            'SSP' => array( 'SS' ),
            'LKR' => array( 'LK' ),
            'SDG' => array( 'SD' ),
            'SRD' => array( 'SR' ),
            'SZL' => array( 'SZ' ),
            'SEK' => array( 'SE' ),
            'SYP' => array( 'SY' ),
            'TWD' => array( 'TW' ),
            'TJS' => array( 'TJ' ),
            'TZS' => array( 'TZ' ),
            'THB' => array( 'TH' ),
            'TOP' => array( 'TO' ),
            'TTD' => array( 'TT' ),
            'TND' => array( 'TN' ),
            'TRY' => array( 'TR' ),
            'TMT' => array( 'TM' ),
            'UGX' => array( 'UG' ),
            'UAH' => array( 'UA' ),
            'AED' => array( 'AE' ),
            'UYU' => array( 'UY' ),
            'UZS' => array( 'UZ' ),
            'VUV' => array( 'VU' ),
            'VEF' => array( 'VE' ),
            'VND' => array( 'VN' ),
            'YER' => array( 'YE' ),
            'ZMW' => array( 'ZM' ),
            'ZWD' => array( 'ZW' ),
        );
    }

    function get_ams_woocommerce_currency_code( $country = '' ) {
        // Set default
        $default = null;
        $country_code = "USD";

        // If country not exists
        if ( ! $country ) return $default;

        // If country is "com" which is mainly refers to us
        if( $country == 'com' ) return $country_code;

        // Search if country exists return the country code
        $currency_countries = ams_woocommerce_currency_countries();
        foreach ( $currency_countries as $country_code => $countries ) {
            if(in_array(strtoupper($country), $countries)) {
                return $country_code;
            }
        }

        // If not esists return default value
        return $default;
    }

    /* Simple Woocommerce Stuff */
    function wc_simple_create_attributes( $name, $options ){
        $attribute = new \WC_Product_Attribute();
        $attribute->set_id(0);
        $attribute->set_name($name);
        $attribute->set_options($options);
        $attribute->set_visible(true);
        $attribute->set_variation(true);
        return $attribute;
    }

    function wc_simple_create_variations($product_id, $values, $data = []) {
        if (!isset($data['sku']) || !isset($data['attributes'])) {
            return;
        }
        $product = wc_get_product($product_id);
        $slug = '';
        if (!empty($values)) {
            $slug = '-' . strtolower(implode('-', array_values($values)));
        }
        $variation_post = array(
            'post_title'  => $data['post_title'],
            'post_name'   => 'product-' . $product_id . '-variation' . $slug,
            'post_status' => 'publish',
            'post_parent' => $product_id,
            'post_type'   => 'product_variation',
            'guid'        => $product->get_permalink()
        );
        $existing_variation = get_post($variation_post);
        // CreateOrUpdate the product variation
        if ($existing_variation !== null) {
            $variation_id = $existing_variation->ID;
            $variation_post['ID'] = $existing_variation->ID;
            wp_update_post($variation_post);                        
        } else {
            $variation_id = wp_insert_post($variation_post);
        }
        // Get an instance of the WC_Product_Variation object
        $variation = new \WC_Product_Variation($variation_id);
        // Add variation scraped data to meta
        $variation->update_meta_data('variation_data', $data);
        $variation->set_attributes($values);
        $variation->set_status('publish');
        if (!empty($data)) {
            // Set defaults
            $regular_price = isset($data['regular_price']) ? $data['regular_price'] : '';
            $sale_price = isset($data['sale_price']) ? $data['sale_price'] : '';

            // Convert '0' to empty string for prices
            $regular_price = ($regular_price === '0') ? '' : $regular_price;
            $sale_price = ($sale_price === '0') ? '' : $sale_price;

            // Set regular price
            if (!empty($regular_price)) {
                $variation->set_regular_price($regular_price);
            }

            // Set sale price and current price
            if (!empty($sale_price) && $sale_price < $regular_price) {
                $variation->set_sale_price($sale_price);
                $variation->set_price($sale_price);
            } else {
                $variation->set_sale_price(''); // Clear sale price if it's not valid
                $variation->set_price($regular_price);
            }

            // Stock
            if (!empty($data['stock_status'])) {
                $variation->set_stock_status($data['stock_status']);
            } else {
                $variation->set_manage_stock(false);
            }
        }
        $variation->save();
        $product->save();
    }

    function get_product_by_sku( $sku ) {
        global $wpdb;

        $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
        if ( $product_id ) {
            return wc_get_product($product_id);
        }

        return null;
    }

    //Create function - original
    if ( ! function_exists( 'wc_create_product_variations' ) ) {
        function wc_create_product_variations($product_id, $product_variations, $parentSku) {
            global $wpdb;

            $product = wc_get_product($product_id);
            if (!$product || !is_a($product, 'WC_Product')) {
                return false;
            }

            try {
                // Store existing attributes for preservation
                $existing_attributes = $product->get_attributes();
                $parent_attributes = [];
                
                // Preserve existing attributes
                foreach ($existing_attributes as $attribute_name => $attribute_value) {
                    if (strtolower($attribute_name) === 'brand') {
                        $parent_attributes['brand'] = $attribute_value;
                    }
                }

                // Update main product SKU if different
                if (!empty($parentSku) && $product->get_sku() !== $parentSku) {
                    $product->set_sku($parentSku);
                    $product->save();
                }

                $updated_variation_ids = [];
                $all_variants_skipped = true;

                // Retrieve setting whether to remove unavailable products
                $remove_unavailable_products = (get_option('ams_remove_unavailable_products') === 'Yes');

                // Loop through new variations
                foreach ($product_variations as $variation_index => $variation_data) {
                    // Skip zero-price+outofstock if setting is enabled
                    if (
                        $remove_unavailable_products &&
                        (empty($variation_data['regular_price']) || floatval($variation_data['regular_price']) == 0) &&
                        (empty($variation_data['sale_price']) || floatval($variation_data['sale_price']) == 0) &&
                        (isset($variation_data['stock_status']) && $variation_data['stock_status'] === 'outofstock')
                    ) {
                        $sku = isset($variation_data['sku']) ? $variation_data['sku'] : 'Unknown SKU';
                        logImportVerification("Variation SKU: {$sku} skipped. Zero prices and out of stock.");
                        continue;
                    }

                    $all_variants_skipped = false;

                    if (empty($variation_data['attributes'])) {
                        continue;
                    }

                    // Create new variation
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($product_id);

                    // Handle SKU with improved checking
                    if (!empty($variation_data['sku'])) {
                        $sanitized_sku = sanitize_text_field($variation_data['sku']);
                        $existing_id = wc_get_product_id_by_sku($sanitized_sku);
                        if ($existing_id) {
                            logImportVerification("Variation SKU: {$sanitized_sku} skipped. Duplicate SKU.");
                            continue;
                        }
                        $variation->set_sku($sanitized_sku);
                    } else {
                        logImportVerification("Variation without SKU skipped.");
                        continue;
                    }

                    // Set prices
                    $variation->set_regular_price($variation_data['regular_price']);
                    if (!empty($variation_data['sale_price']) && floatval($variation_data['sale_price']) < floatval($variation_data['regular_price'])) {
                        $variation->set_sale_price($variation_data['sale_price']);
                        $variation->set_price($variation_data['sale_price']);
                    } else {
                        $variation->set_sale_price('');
                        $variation->set_price($variation_data['regular_price']);
                    }

                    // Stock
                    if (isset($variation_data['stock_qty']) && trim($variation_data['stock_qty']) > 0) {
                        $variation->set_stock_quantity($variation_data['stock_qty']);
                        $variation->set_manage_stock(true);
                        $variation->set_stock_status($variation_data['stock_status']);
                    } else {
                        $variation->set_manage_stock(false);
                        $variation->set_stock_status($variation_data['stock_status']);
                    }

                    // Descriptions
                    if (!empty($variation_data['description'])) {
                        $variation->set_description($variation_data['description']);
                    }
                    if (!empty($variation_data['additional_description'])) {
                        $variation->update_meta_data('_additional_description', $variation_data['additional_description']);
                    }

                    // Build an associative array of selected attribute values
                    $selected_attributes = array();
                    foreach ($variation_data['attributes'] as $attr) {
                        if (isset($attr['slug']) && isset($attr['selected']) && !empty($attr['selected'])) {
                            $selected_attributes[wc_sanitize_taxonomy_name($attr['slug'])] = $attr['selected'];
                        }
                    }
                    if (empty($selected_attributes)) {
                        logImportVerification("No selected attributes found for SKU: " . $variation_data['sku']);
                        continue;
                    }

                    $variation_attributes = array();
                    foreach ($variation_data['attributes'] as $attribute) {
                        $attribute_slug = wc_sanitize_taxonomy_name($attribute['slug']);
                        $attribute_key = 'pa_' . $attribute_slug;

                        // Ensure taxonomy exists
                        if (!taxonomy_exists($attribute_key)) {
                            $attribute_id = wc_create_attribute(array(
                                'name' => wc_clean($attribute['name']),
                                'slug' => $attribute_slug,
                                'type' => 'select',
                                'order_by' => 'menu_order',
                                'has_archives' => false,
                            ));
                            if (!is_wp_error($attribute_id)) {
                                register_taxonomy(
                                    $attribute_key,
                                    array('product'),
                                    array(
                                        'label' => wc_clean($attribute['name']),
                                        'rewrite' => array('slug' => $attribute_slug),
                                        'hierarchical' => false,
                                    )
                                );
                            }
                        }

                        // Update or create the parent product attribute
                        if (!isset($parent_attributes[$attribute_key])) {
                            $parent_attribute = new WC_Product_Attribute();
                            $parent_attribute->set_id(wc_attribute_taxonomy_id_by_name($attribute_key));
                            $parent_attribute->set_name($attribute_key);
                            $parent_attribute->set_visible($attribute['visible']);
                            $parent_attribute->set_variation($attribute['variation']);

                            $options = (isset($attribute['options']) && is_array($attribute['options']) && !empty($attribute['options']))
                                ? $attribute['options']
                                : array($selected_attributes[$attribute_slug]);
                            $term_ids = array();
                            foreach ($options as $option) {
                                $term_name = wc_clean($option);
                                $term = get_term_by('name', $term_name, $attribute_key);
                                if (!$term) {
                                    $term = wp_insert_term($term_name, $attribute_key);
                                    if (!is_wp_error($term)) {
                                        $term = get_term($term['term_id'], $attribute_key);
                                    }
                                }
                                if ($term && !is_wp_error($term)) {
                                    $term_ids[] = $term->term_id;
                                }
                            }
                            $parent_attribute->set_options($term_ids);
                            $parent_attributes[$attribute_key] = $parent_attribute;
                        }

                        // Use the selected value for this attribute
                        if (!isset($selected_attributes[$attribute_slug])) {
                            logImportVerification("Missing desired value for attribute '{$attribute_slug}' for SKU " . $variation_data['sku']);
                            continue 2;
                        }
                        $desired_value = $selected_attributes[$attribute_slug];

                        // Get allowed options; fallback if missing
                        $options = (isset($attribute['options']) && is_array($attribute['options']) && !empty($attribute['options']))
                            ? $attribute['options']
                            : array($desired_value);

                        $found = false;
                        foreach ($options as $option) {
                            if (strcasecmp($option, $desired_value) === 0) {
                                $found = true;
                                $term_name = wc_clean($option);
                                break;
                            }
                        }
                        if (!$found) {
                            logImportVerification("Desired value '{$desired_value}' not found for attribute '{$attribute_slug}' in SKU " . $variation_data['sku']);
                            continue 2;
                        }

                        $term = get_term_by('name', $term_name, $attribute_key);
                        if ($term && !is_wp_error($term)) {
                            $variation_attributes['attribute_' . $attribute_key] = $term->slug;
                        }

                        logImportVerification("For SKU {$variation_data['sku']}, attribute '{$attribute_slug}' selected value: {$desired_value}");
                    }

                    // Save the variation 
                    $variation->set_attributes($variation_attributes);
                    $variation->save();
                    $variation_id = $variation->get_id();

                    // Handle images
                    if (!empty($variation_data['product_image_gallery'])) {
                        $gallery = $variation_data['product_image_gallery'];
                        if (!is_array($gallery)) {
                            $gallery = array($gallery);
                        }
                        $gallery_image_ids = array();
                        $local_gallery_urls = array();
                        $use_remote_images = ('Yes' === get_option('ams_remote_amazon_images'));
                        foreach ($gallery as $index => $image) {
                            if ($use_remote_images) {
                                $image_ids = attach_product_thumbnail_url($variation_id, $image, ($index === 0 ? 0 : 1));
                                if (is_array($image_ids)) {
                                    $gallery_image_ids = array_merge($gallery_image_ids, $image_ids);
                                } elseif ($image_ids) {
                                    $gallery_image_ids[] = $image_ids;
                                }
                                $local_gallery_urls[] = $image;
                            } else {
                                $local_url = attach_product_thumbnail($variation_id, $image, ($index === 0 ? 0 : 1));
                                if ($local_url) {
                                    $local_gallery_urls[] = $local_url;
                                    $attach_id = attachment_url_to_postid($local_url);
                                    if ($attach_id) {
                                        $gallery_image_ids[] = $attach_id;
                                    }
                                }
                            }
                        }
                        if (!empty($gallery_image_ids)) {
                            set_post_thumbnail($variation_id, $gallery_image_ids[0]);
                            update_post_meta($variation_id, '_product_image_gallery', implode(',', array_unique($gallery_image_ids)));
                        }
                        update_post_meta($variation_id, '_amswoofiu_wcgallary', array_unique($local_gallery_urls));
                    }

                    $updated_variation_ids[] = $variation_id;
                }

                // Handle if all variants were skipped
                if ($all_variants_skipped && $remove_unavailable_products) {
                    wp_delete_post($product_id, true);
                    logImportVerification("Product ID: {$product_id} removed. All variants had zero prices or were out of stock.");
                    return new WP_Error('all_variants_skipped', esc_html__("OutOfStock!", 'ams-wc-amazon'));
                }

                // Set updated attributes for parent
                $product->set_attributes(array_values($parent_attributes));
                $product->save();

                // Clear caches
                wc_delete_product_transients($product_id);
                wp_cache_delete($product_id, 'posts');
                wp_cache_delete($product_id, 'product_' . $product_id);
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}wc_product_meta_lookup WHERE product_id = %d", $product_id));

                return true;

            } catch (Exception $e) {
                logImportVerification("Error in create_product_variations: " . $e->getMessage());
                return false;
            }
        }
    }

    //Update function - original
    if ( ! function_exists( 'wc_update_product_variations' ) ) {
        function wc_update_product_variations( $product_id, $product_variations, $parentSku ) {
            global $wpdb;

            $product = wc_get_product( $product_id );
            if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
                return false;
            }

            // Get all existing variation IDs for this product.
            $variation_ids = $product->get_children();

            // Clear image meta for all variations.
            foreach ( $variation_ids as $variation_id ) {
                $thumbnail_id = get_post_meta( $variation_id, '_thumbnail_id', true );
                $gallery_image_ids = explode( ',', get_post_meta( $variation_id, '_product_image_gallery', true ) );
                if ( $thumbnail_id ) {
                    wp_delete_attachment( $thumbnail_id, true );
                }
                foreach ( $gallery_image_ids as $image_id ) {
                    if ( $image_id ) {
                        wp_delete_attachment( $image_id, true );
                    }
                }
                delete_post_meta( $variation_id, '_thumbnail_id' );
                delete_post_meta( $variation_id, '_product_image_gallery' );
            }

            // Update main product SKU if necessary.
            if ( ! empty( $parentSku ) && $product->get_sku() !== $parentSku ) {
                $product->set_sku( $parentSku );
                $product->save();
            }

            // Remove all existing variations.
            foreach ( $variation_ids as $variation_id ) {
                wp_delete_post( $variation_id, true );
            }

            $parent_attributes = array();
            $all_variants_skipped = true;
            $remove_unavailable_products = get_option( 'ams_remove_unavailable_products' ) === 'Yes';

            foreach ( $product_variations as $variation_index => $variation_data ) {
                // Skip variants with zero prices and out of stock if setting is enabled.
                if ( $remove_unavailable_products &&
                    ( empty( $variation_data['regular_price'] ) || floatval( $variation_data['regular_price'] ) == 0 ) &&
                    ( empty( $variation_data['sale_price'] ) || floatval( $variation_data['sale_price'] ) == 0 ) &&
                    ( isset( $variation_data['stock_status'] ) && $variation_data['stock_status'] === 'outofstock' )
                ) {
                    $sku = isset( $variation_data['sku'] ) ? $variation_data['sku'] : 'Unknown SKU';
                    logImportVerification( "Variation SKU: {$sku} skipped. Zero prices and out of stock." );
                    continue;
                }

                $all_variants_skipped = false;
                if ( empty( $variation_data['attributes'] ) ) {
                    continue;
                }

                $variation = new WC_Product_Variation();
                $variation->set_parent_id( $product_id );

                // Set SKU.
                if ( ! empty( $variation_data['sku'] ) ) {
                    $sanitized_sku = sanitize_text_field( $variation_data['sku'] );
                    if ( wc_get_product_id_by_sku( $sanitized_sku ) ) {
                        logImportVerification( "Variation SKU: {$sanitized_sku} skipped. Duplicate SKU." );
                        continue;
                    }
                    $variation->set_sku( $sanitized_sku );
                } else {
                    logImportVerification( "Variation without SKU skipped." );
                    continue;
                }

                // Set pricing.
                $variation->set_regular_price( $variation_data['regular_price'] );
                if ( ! empty( $variation_data['sale_price'] ) && floatval( $variation_data['sale_price'] ) < floatval( $variation_data['regular_price'] ) ) {
                    $variation->set_sale_price( $variation_data['sale_price'] );
                    $variation->set_price( $variation_data['sale_price'] );
                } else {
                    $variation->set_sale_price( '' );
                    $variation->set_price( $variation_data['regular_price'] );
                }

                // Set stock information.
                if ( isset( $variation_data['stock_qty'] ) && trim( $variation_data['stock_qty'] ) > 0 ) {
                    $variation->set_stock_quantity( $variation_data['stock_qty'] );
                    $variation->set_manage_stock( true );
                    $variation->set_stock_status( $variation_data['stock_status'] );
                } else {
                    $variation->set_manage_stock( false );
                    $variation->set_stock_status( $variation_data['stock_status'] );
                }

                // Set descriptions.
                if ( ! empty( $variation_data['description'] ) ) {
                    $variation->set_description( $variation_data['description'] );
                }
                if ( ! empty( $variation_data['additional_description'] ) ) {
                    $variation->update_meta_data( '_additional_description', $variation_data['additional_description'] );
                }

                // Build an associative array of selected attribute values.
                $selected_attributes = array();
                foreach ( $variation_data['attributes'] as $attr ) {
                    if ( isset( $attr['slug'] ) && isset( $attr['selected'] ) && ! empty( $attr['selected'] ) ) {
                        $selected_attributes[ wc_sanitize_taxonomy_name( $attr['slug'] ) ] = $attr['selected'];
                    }
                }
                if ( empty( $selected_attributes ) ) {
                    logImportVerification( "No selected attributes found for SKU: " . $variation_data['sku'] );
                    continue;
                }

                $variation_attributes = array();
                foreach ( $variation_data['attributes'] as $attribute ) {
                    $attribute_slug = wc_sanitize_taxonomy_name( $attribute['slug'] );
                    $attribute_key  = 'pa_' . $attribute_slug;

                    // Ensure taxonomy exists.
                    if ( ! taxonomy_exists( $attribute_key ) ) {
                        $attribute_id = wc_create_attribute( array(
                            'name'         => wc_clean( $attribute['name'] ),
                            'slug'         => $attribute_slug,
                            'type'         => 'select',
                            'order_by'     => 'menu_order',
                            'has_archives' => false,
                        ) );
                        if ( ! is_wp_error( $attribute_id ) ) {
                            register_taxonomy(
                                $attribute_key,
                                array( 'product' ),
                                array(
                                    'label'        => wc_clean( $attribute['name'] ),
                                    'rewrite'      => array( 'slug' => $attribute_slug ),
                                    'hierarchical' => false,
                                )
                            );
                        }
                    }

                    // Update or create the parent product attribute.
                    if ( ! isset( $parent_attributes[ $attribute_key ] ) ) {
                        $parent_attribute = new WC_Product_Attribute();
                        $parent_attribute->set_id( wc_attribute_taxonomy_id_by_name( $attribute_key ) );
                        $parent_attribute->set_name( $attribute_key );
                        $parent_attribute->set_visible( $attribute['visible'] );
                        $parent_attribute->set_variation( $attribute['variation'] );

                        $options = ( isset( $attribute['options'] ) && is_array( $attribute['options'] ) && ! empty( $attribute['options'] ) )
                            ? $attribute['options']
                            : array( $selected_attributes[ $attribute_slug ] );
                        $term_ids = array();
                        foreach ( $options as $option ) {
                            $term_name = wc_clean( $option );
                            $term = get_term_by( 'name', $term_name, $attribute_key );
                            if ( ! $term ) {
                                $term = wp_insert_term( $term_name, $attribute_key );
                                if ( ! is_wp_error( $term ) ) {
                                    $term = get_term( $term['term_id'], $attribute_key );
                                }
                            }
                            if ( $term && ! is_wp_error( $term ) ) {
                                $term_ids[] = $term->term_id;
                            }
                        }
                        $parent_attribute->set_options( $term_ids );
                        $parent_attributes[ $attribute_key ] = $parent_attribute;
                    } else {
                        // Ensure the existing parent attribute includes the selected value.
                        $existing_options = $parent_attributes[ $attribute_key ]->get_options();
                        $selected_value = $selected_attributes[ $attribute_slug ];
                        $term = get_term_by( 'name', wc_clean( $selected_value ), $attribute_key );
                        if ( ! $term ) {
                            $term = wp_insert_term( wc_clean( $selected_value ), $attribute_key );
                            if ( ! is_wp_error( $term ) ) {
                                $term = get_term( $term['term_id'], $attribute_key );
                            }
                        }
                        if ( $term && ! is_wp_error( $term ) ) {
                            if ( ! in_array( $term->term_id, $existing_options ) ) {
                                $existing_options[] = $term->term_id;
                                $parent_attributes[ $attribute_key ]->set_options( $existing_options );
                            }
                        }
                    }

                    // Use the selected value for this attribute.
                    if ( ! isset( $selected_attributes[ $attribute_slug ] ) ) {
                        logImportVerification( "Missing desired value for attribute '{$attribute_slug}' for SKU " . $variation_data['sku'] );
                        continue 2;
                    }
                    $desired_value = $selected_attributes[ $attribute_slug ];

                    // Get allowed options; fallback if missing.
                    $options = ( isset( $attribute['options'] ) && is_array( $attribute['options'] ) && ! empty( $attribute['options'] ) )
                        ? $attribute['options']
                        : array( $desired_value );

                    $found = false;
                    foreach ( $options as $option ) {
                        if ( strcasecmp( $option, $desired_value ) === 0 ) {
                            $found = true;
                            $term_name = wc_clean( $option );
                            break;
                        }
                    }
                    if ( ! $found ) {
                        logImportVerification( "Desired value '{$desired_value}' not found for attribute '{$attribute_slug}' in SKU " . $variation_data['sku'] );
                        continue 2;
                    }

                    $term = get_term_by( 'name', $term_name, $attribute_key );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $variation_attributes[ 'attribute_' . $attribute_key ] = $term->slug;
                    }

                    logImportVerification( "For SKU {$variation_data['sku']}, attribute '{$attribute_slug}' selected value: {$desired_value}" );
                }

                // Log computed attributes.
                logImportVerification( sprintf(
                    "Setting variation attributes for SKU %s: %s",
                    $variation_data['sku'],
                    print_r( $variation_attributes, true )
                ) );

                $variation->set_attributes( $variation_attributes );
                $variation->save();

                // Get new variation ID for images.
                $new_variation_id = $variation->get_id();

                // Handle images.
                if ( ! empty( $variation_data['product_image_gallery'] ) ) {
                    $gallery = $variation_data['product_image_gallery'];
                    if ( ! is_array( $gallery ) ) {
                        $gallery = array( $gallery );
                    }
                    $gallery_image_ids = array();
                    $local_gallery_urls = array();
                    $use_remote_images = ( 'Yes' === get_option( 'ams_remote_amazon_images' ) );
                    foreach ( $gallery as $index => $image ) {
                        if ( $use_remote_images ) {
                            $image_ids = attach_product_thumbnail_url( $new_variation_id, $image, ( $index === 0 ? 0 : 1 ) );
                            if ( is_array( $image_ids ) ) {
                                $gallery_image_ids = array_merge( $gallery_image_ids, $image_ids );
                            } elseif ( $image_ids ) {
                                $gallery_image_ids[] = $image_ids;
                            }
                            $local_gallery_urls[] = $image;
                        } else {
                            $local_url = attach_product_thumbnail( $new_variation_id, $image, ( $index === 0 ? 0 : 1 ) );
                            if ( $local_url ) {
                                $local_gallery_urls[] = $local_url;
                                $attach_id = attachment_url_to_postid( $local_url );
                                if ( $attach_id ) {
                                    $gallery_image_ids[] = $attach_id;
                                }
                            }
                        }
                    }
                    if ( ! empty( $gallery_image_ids ) ) {
                        set_post_thumbnail( $new_variation_id, $gallery_image_ids[0] );
                        update_post_meta( $new_variation_id, '_product_image_gallery', implode( ',', array_unique( $gallery_image_ids ) ) );
                    }
                }
            }

            if ( $all_variants_skipped && $remove_unavailable_products ) {
                wp_delete_post( $product_id, true );
                logImportVerification( "Product ID: {$product_id} removed. All variants had zero prices or were out of stock." );
                return new WP_Error( 'all_variants_skipped', esc_html__( "OutOfStock!", 'ams-wc-amazon' ) );
            }

            // Preserve existing brand attribute.
            $existing_attributes = $product->get_attributes();
            foreach ( $existing_attributes as $attribute_name => $attribute_value ) {
                if ( strtolower( $attribute_name ) === 'brand' && ! isset( $parent_attributes['brand'] ) ) {
                    $parent_attributes['brand'] = $attribute_value;
                }
            }

            $product->set_attributes( array_values( $parent_attributes ) );
            $product->save();

            wc_delete_product_transients( $product_id );
            // Optionally flush rewrite rules only if needed
            // flush_rewrite_rules();

            return true;
        }
    }

    function generate_amazon_url_1($product_url, $variation_id, $use_th = false) {
        // Construct the URL for the variation:
        $base_url = get_parsed_url($product_url, 'PHP_URL_BASE');
        if (strpos($variation_id, '#') !== false) {
            list($custom_token, $custom_id) = explode('#', $variation_id);
            $base_url .= "/dp/{$custom_id}?customId={$custom_id}&customizationToken=" . urlencode($variation_id) . "&th=1&psc=1";
        } else {
            $base_url .= '/dp/' . $variation_id . '/?th=1&psc=1';
        }
        return $base_url;
    }

    function generate_amazon_url_2($product_url, $variation_id) {

        // Extract the base domain
        $base_url = get_parsed_url($product_url, 'PHP_URL_BASE');

        // Attempt to extract the parent ASIN from the product URL
        $parent_asin = '';
        if (preg_match('/\/dp\/(B[A-Z0-9]+)/', $product_url, $matches)) {
            $parent_asin = $matches[1];
        }

        // If we found a parent ASIN, build the link using it
        if ($parent_asin) {
            // Example: https://www.amazon.co.uk/dp/B0DT3HH3M8/?customId=B075386ZX4
            $base_url .= "/dp/{$parent_asin}/?customId={$variation_id}";
        } else {
            // Fallback: if no parent ASIN was found, just use the variation_id itself
            $base_url .= "/dp/{$variation_id}/?customId={$variation_id}";
        }

        return $base_url;
    }

    function get_parsed_url($url, $part = 'PHP_URL_FULL') {
        $parse = parse_url($url);
        
        switch ($part) {
            case 'PHP_URL_FULL':
                $formatted = $parse['scheme'] . '://' . $parse['host'];
                if (isset($parse['path'])) {
                    $formatted .= $parse['path'];
                }
                if (isset($parse['query'])) {
                    $formatted .= '?' . $parse['query'];
                }
                break;
            case 'PHP_URL_BASE':
                $formatted = $parse['scheme'] . '://' . $parse['host'];
                break;
            default:
                $formatted = '';
        }
        
        // Check for empty string then return false
        if ($formatted == '') return FALSE;
        return $formatted;
    }

function generate_unique_sku($base_sku, $product_id) {
    $counter = 1;
    $new_sku = $base_sku;
    while (wc_get_product_id_by_sku($new_sku)) {
        $new_sku = $base_sku . '-' . $counter;
        $counter++;
    }
    return $new_sku;
}

function get_variation_by_sku($sku, $parentSku) {
    global $wpdb;
    
    $query = $wpdb->prepare(
        "SELECT p.ID 
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_sku' 
        AND pm.meta_value = %s
        AND p.post_type = 'product_variation'
        AND p.ID NOT IN (
            SELECT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_sku' AND meta_value = %s
        )
        LIMIT 1",
        $sku,
        $parentSku
    );
    
    $variation_id = $wpdb->get_var($query);
    
    if ($variation_id) {
        return wc_get_product($variation_id);
    }
    
    return null;
}

//wf
function wc_update_product_attributes( $product_id, $attributes_data ) {
    if( empty($attributes_data) || !is_array($attributes_data) ) {
        error_log("No valid attributes data provided for product ID: $product_id");
        return false;
    }

    $attributes = array();
    foreach( $attributes_data as $key => $attribute_array ) {
        if( isset($attribute_array['name']) && isset($attribute_array['options']) ){
            $attribute_name = wc_clean( $attribute_array['name'] );
            $attribute_slug = !empty($attribute_array['slug']) ? wc_sanitize_taxonomy_name( $attribute_array['slug'] ) : wc_sanitize_taxonomy_name( $attribute_name );
            $taxonomy = 'pa_' . $attribute_slug;

            if( ! taxonomy_exists( $taxonomy ) ) {
                $attribute_id = wc_create_attribute( array('name' => $attribute_name, 'slug' => $attribute_slug) );
                if ( is_wp_error( $attribute_id ) ) {
                    error_log("Failed to create attribute: " . $attribute_id->get_error_message());
                    continue;
                }
                register_taxonomy(
                    $taxonomy,
                    apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
                    apply_filters( 'woocommerce_taxonomy_args_' . $taxonomy, array(
                        'labels'       => array('name' => $attribute_name),
                        'hierarchical' => true,
                        'show_ui'      => false,
                        'query_var'    => true,
                        'rewrite'      => false,
                    ) )
                );
                delete_transient( 'wc_attribute_taxonomies' );
            }

            $option_term_ids = array();
            foreach( $attribute_array['options'] as $option ) {
                $option = wc_clean( $option );

                // Check if term exists or insert a new term
                $term = term_exists( $option, $taxonomy );
                if ( !$term ) {
                    $term = wp_insert_term( $option, $taxonomy );
                }

                // Handle errors
                if ( is_wp_error( $term ) ) {
                    error_log("Failed to insert term: " . $term->get_error_message());
                    continue;
                }

                // Ensure term exists and get the term_id
                if ( is_array( $term ) ) {
                    $term_id = $term['term_id'];
                } elseif ( is_object( $term ) && property_exists( $term, 'term_id' ) ) {
                    $term_id = $term->term_id;
                } else {
                    error_log("Unexpected term format for option: $option in taxonomy: $taxonomy");
                    continue;
                }

                // Assign term to the product
                wp_set_object_terms( $product_id, $term_id, $taxonomy, true );
                $option_term_ids[] = $term_id;
            }
            
            $attribute = new WC_Product_Attribute();
            $attribute->set_name( $taxonomy );
            $attribute->set_options( $option_term_ids );
            $attribute->set_visible( isset($attribute_array['visible']) ? wc_string_to_bool($attribute_array['visible']) : false );
            $attribute->set_variation( isset($attribute_array['variation']) ? wc_string_to_bool($attribute_array['variation']) : false );
            
            $attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
            $attribute->set_id( $attribute_id );

            $attributes[$taxonomy] = $attribute;
        }
    }

    $product = wc_get_product( $product_id );
    $product->set_attributes( $attributes );
    $save_result = $product->save();

    if ( is_wp_error( $save_result ) ) {
        error_log("Failed to save product attributes: " . $save_result->get_error_message());
        return false;
    }

    return true;
}


add_filter('woocommerce_available_variation', 'bbloomer_add_price_variation_data');
function bbloomer_add_price_variation_data($variations) {
    $variation = wc_get_product($variations['variation_id']);

    if ($variation) {
        // Regular price
        $regular_price = $variation->get_regular_price();
        $variations['formatted_regular_price'] = '<div class="woocommerce_regular_price">Regular Price: <span>' . wc_price($regular_price) . '</span></div>';

        // Sale price (if applicable)
        if ($variation->is_on_sale()) {
            $sale_price = $variation->get_sale_price();
            $variations['formatted_sale_price'] = '<div class="woocommerce_sale_price">Sale Price: <span>' . wc_price($sale_price) . '</span></div>';
        }

        // Keep the additional information
        $additional_info = get_post_meta($variations['variation_id'], '_ams_additional_information', true);
        $variations['_ams_additional_information'] = '<div class="ams_wc_additional_information">' . $additional_info . '</div>';
    }

    return $variations;
}

/* END OF ADMIN STUFF */

// Update variation price display area in single product page
add_action('woocommerce_variable_add_to_cart', 'bbloomer_update_price_with_variation_price');
function bbloomer_update_price_with_variation_price() {
    global $product;
    if ('variable' !== $product->get_type()) {
        return;
    }
    $price = $product->get_price_html();
    $ams_additional_information = get_post_meta($product->get_id(), '_ams_additional_information', true);
    $ams_additional_information = wp_kses_post($ams_additional_information);

    $variations_data = array();
    foreach ($product->get_available_variations() as $variation) {
        $variation_id = $variation['variation_id'];
        $variation_obj = wc_get_product($variation_id);
        $variation_info = get_post_meta($variation_id, '_ams_additional_information', true);
        
        if (!$variation_info) {
            $variation_info = $ams_additional_information;
            
            // Modify size information
            $size = $variation['attributes']['attribute_pa_size'] ?? '';
            if ($size) {
                $variation_info = preg_replace(
                    '/(Taille<\/span><\/td><td><span>).*?(<\/span>)/',
                    '$1Taille ' . strtoupper($size) . '$2',
                    $variation_info
                );
            }
            
            // Modify unit count
            $unit_count = $variation_obj->get_stock_quantity();
            if ($unit_count) {
                $variation_info = preg_replace(
                    '/(Nombre d\'unités<\/span><\/td><td><span>).*?(<\/span>)/',
                    '$1' . $unit_count . ' unité' . ($unit_count > 1 ? 's' : '') . '$2',
                    $variation_info
                );
            }
        }
        
        $variations_data[$variation_id] = wp_kses_post($variation_info);
        
        // Debug output
        // echo "Variant ID: $variation_id<br>";
        // echo "Content: " . esc_html($variation_info) . "<br><br>";
    }

    wc_enqueue_js("
        var variationsData = " . json_encode($variations_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) . ";
        var defaultInfo = " . json_encode($ams_additional_information, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) . ";
        
        function updateAdditionalInfo(content) {
            var infoDiv = document.getElementById('ams-additional-information');
            if (!infoDiv) {
                infoDiv = document.createElement('div');
                infoDiv.id = 'ams-additional-information';
                document.querySelector('.summary').appendChild(infoDiv);
            }
            infoDiv.innerHTML = content;
        }
        
        jQuery(document).ready(function() {
            updateAdditionalInfo(defaultInfo);
        });
        
        jQuery(document).on('found_variation', 'form.variations_form', function(event, variation) {
            if(variation.price_html) jQuery('.summary > p.price').html(variation.price_html);
            if(variationsData[variation.variation_id]) updateAdditionalInfo(variationsData[variation.variation_id]);
        });
        
        jQuery(document).on('reset_data', 'form.variations_form', function() {
            jQuery('.summary > p.price').html(" . json_encode($price) . ");
            updateAdditionalInfo(defaultInfo);
        });
    ");

    // Debug: Print out the final variations data
    // echo "<pre>";
    // print_r($variations_data);
    // echo "</pre>";
}


// Remove reset link from single page
add_filter( 'woocommerce_reset_variations_link', '__return_empty_string', 9999 );
function buildPriceHtml( $from, $to = '' ) {
    if( !empty($from) && !empty($to) ) {
        return sprintf("<del aria-hidden=\"true\"><span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span></del><ins><span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span></ins>",$from,$to);
    } else if( !empty($from) ) {
        return sprintf("<span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span>",$from);
    } else {
        return sprintf("<span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span>",$to);
    }
}

function buildVariationPriceHtml( $min, $max = '' ) {
    if( !empty($min) && !empty($max) ) {
        return sprintf("<span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span> – <span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span>",$min,$max);
    } else if( !empty($min) ){
        return sprintf("<span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span>",$min);
    } else {
        return sprintf("<span class=\"woocommerce-Price-amount amount\"><bdi>%s</bdi></span>",$max);
    }
}

if (!function_exists('dd')) {
    function dd($d) {
        echo '<pre>';
        print_r($d);
        echo '</pre>';
    }
}

function wc_plugin_styles() { ?>
<style>.woocommerce-variation-add-to-cart-disabled {display:none; !important;} body.single-product .woocommerce .woocommerce-tabs .woocommerce-Tabs-panel h2, .woocommerce .woocommerce-tabs .woocommerce-Tabs-panel h2 { display: none !important; }</style>
<?php }
add_action('wp_head','wc_plugin_styles');

function ams_script_to_footer() {    
    wc_enqueue_js("
    jQuery(document).ready(function($){
      if( $('#tab-description #aplus_feature_div img').length > 0 ) {
        $('#tab-description #aplus_feature_div img').each(function(index, image) {
          if( $(this).data('src') ) {
            $(this).attr('src', $(this).data('src'));
          }
        });
      }
      if( $('#tab-description .aplus-v2 img').length > 0 ) {
        $('#tab-description .aplus-v2 img').each(function(index, image) {
          if( $(this).data('src') ) {
            $(this).attr('src', $(this).data('src'));
          }
        });
      }      
    });
    ");
}
add_action('wp_footer', 'ams_script_to_footer');
add_filter( 'wc_product_enable_dimensions_display', '__return_true' );


// Add Import method field
add_action( 'woocommerce_product_options_stock_status', 'display_product_options_inventory_custom_fields', 20 );
function display_product_options_inventory_custom_fields() {
    global $post;

    echo '</div><div class="options_group">'; // New separated section

    // Check if the post has a specific meta value
    $specific_meta_value = get_post_meta( $post->ID, '_import_method', true );
    if ( $specific_meta_value ) {

        $import_method = '';
        switch(get_post_meta( $post->ID, '_import_method', true )) {
            case '1':
                $import_method = 'AMAZON API';
                break;
            case '2':
                $import_method = 'NO API - BY SEARCH';
                break;
            case '3':
                $import_method = 'NO API - BY URLS';
                break;
        }

        woocommerce_wp_text_input(
            array(
                'id'          => '_import_method',
                'label'       => __( 'Import method', 'woocommerce' ),
                'placeholder' => '',
                'desc_tip'    => 'true',
                'description' => __( 'Enter a import method.', 'woocommerce' ),
                'value'       => $import_method,
                'custom_attributes'    => array('disabled' => 'disabled', 'readonly' => 'readonly'),
            )
        );
    }
}

/**
 * Check if curl status returns not found we have to mark the existing product status
 * to be out of stock so that when we run cron for atuo clean/remove it will be deleted
 * according to the configuration setting saved
 * 
 * @param string $productUrl
 * @param object $curl
 * 
 * @return boolean
 * 
 */
function updateProductStatusIfNotExists($ch, $productUrl) {
    // Get the response code
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($responseCode == 404) {
        $sku = getSkuFromUrl($productUrl);
        $product = get_product_by_sku($sku);
        // Check If product is null or not found
        if (!$product || is_null($product)) {
            return false;
        }
        // Check If given setting exists
        if ("No" == get_option("ams_remove_unavailable_products")) {
            return false;
        }
        // Set product stock quantity to zero and stock status to out of stock
        $product->set_stock_quantity(0);
        $product->set_stock_status('outofstock');
        // Save the data and refresh caches
        $product->save();
        return true;
    }
    return false;
}

function removeProductIfNotExists($post_id) {
    $product = wc_get_product($post_id);
    
    // Check If product is null or not found
    if (!$product || is_null($product)) {
        return;
    }
    
    // Check If given setting exists
    if ("No" == get_option("ams_remove_unavailable_products")) {
        return;
    }
    
    // Product name
    $title = $product->get_name();
    
    // Handle variable products
    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $childID) {
            $child = wc_get_product($childID);
            if ($child && !$child->is_in_stock()) {
                $child->delete(true); // Delete permanently.
            }
        }
    }
    
    // Save the data and refresh caches
    $product->save();
    
    // Refresh product object
    $_product = wc_get_product($post_id);
    
    // Delete the product if it's out of stock (works for both simple and variable products)
    if ($_product && !$_product->is_in_stock()) {
        $_product->delete(true); // Delete permanently.
    }
}


/**
 * This function will help you to get the SKU/ASIN number from URL
 * 
 * @param string $url
 * @return string
 *  
 */
function getSkuFromUrl($url) {
    $path = parse_url($url, PHP_URL_PATH);
    if ($path === null) {
        return null; // Return null if URL is malformed
    }

    $segments = explode("/dp/", $path);
    if (count($segments) < 2) {
        return null; // Return null if URL does not contain "/dp/"
    }

    $asinSegment = end($segments);
    $asinParts = explode('/', $asinSegment);
    
    return $asinParts[0] ?? null; // Return the ASIN or null if not found
}


/**
 * Count String limit
 * 
 * @param $string
 * @param $limit
 * @param $append
 * 
 * @return string
 */

if ( !function_exists('wp_str_limit') ) {
    function wp_str_limit($string, $limit, $append = '...') {
        if (strlen($string) > $limit) {
            $string = substr($string, 0, $limit) . $append;
        }
        return $string;
    }
}

/**
 * Add dropshipping fee
 * 
 * @param $displayPrice
 * 
 * @return string
 */

 if ( !function_exists('wp_add_dropshipping_fee') ) {
    function wp_add_dropshipping_fee( $displayPrice ) {
        if( empty( $price ) ) {
            return $displayPrice;
        }

        // Extract currency symbol
        preg_match( '/([^\d., ]+)/', $displayPrice, $matches );
        $currencySymbol = $matches[0];

        // Get the currency position
        $before_the_price = false;
        $firstCharacter = substr( $displayPrice, 0, 1 );
        $lastCharacter = substr( $displayPrice, -1 );
        
        if ( is_numeric($firstCharacter) ) {
            $before_the_price = false;
        } elseif (is_numeric($lastCharacter)) {
            $before_the_price = true;
        }

        // Add dropshipping fee
        $price = (float) $displayPrice;
        $percentage_profit = (float) get_option('ams_percentage_profit');
        if( strtolower(get_option('ams_buy_action_btn')) === strtolower('dropship') ) {
            if( !empty( $price ) ) {
                $profit = ( $price / 100 ) * $percentage_profit;
                $price = $price + $profit;
            }
        }

        // Return updated price
        return $before_the_price 
            ? ( $currencySymbol .''. $price ) 
            : ( $price .''. $currencySymbol );
    }
}



function get_amazon_api_details() {
    // Implement this function to return Amazon API details
    // This is just a placeholder. Replace with actual data retrieval.
    return [
        'last_request' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
        'avg_daily_usage' => 1234
    ];
}

function get_scraper_api_details() {
    // Implement this function to return Scraper API details
    // This is just a placeholder. Replace with actual data retrieval.
    return [
        'last_request' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
        'avg_daily_usage' => 567
    ];
}

function get_scrapingant_api_details() {
    // Implement this function to return Scrapingant API details
    // This is just a placeholder. Replace with actual data retrieval.
    return [
        'last_request' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
        'avg_daily_usage' => 789
    ];
}


/**
 * Get the available scraping services configuration with dynamic values.
 * 
 * @return array
 */
function get_scraping_services_config() {
    return array(
        'scrapingant' => array(
            'api_key'    => get_option('ams_scrapingant_api_key', ''),
            'is_active'  => get_option('ams_scrapingant_is_active', '0') === '1',
            'on_update'  => get_option('ams_scrapingant_on_update', '0') === '1',
            'execute'    => 'executeScrapingAntAPI',
        ),
        'scraperapi' => array(
            'api_key'    => get_option('ams_scraper_api_key', ''),
            'is_active'  => get_option('ams_scraper_api_is_active', '0') === '1',
            'on_update'  => get_option('ams_scraper_api_on_update', '0') === '1',
            'execute'    => 'executeScraperAPI',
        ),
            'zyte' => array(
            'api_key'    => get_option('ams_zyte_api_key', ''),
            'is_active'  => get_option('ams_zyte_api_is_active', '0') === '1',
            'on_update'  => get_option('ams_zyte_api_on_update', '0') === '1',
            'execute'    => 'executeZyteAPI',
        ),
    );
}

/**
 * Execute the scraping service based on the configuration.
 * 
 * @param string $productUrl
 * @param bool $onUpdate
 * @return string|boolean
 */

function executeScrapingService($productUrl, $isUpdate = false) {
    $services = get_scraping_services_config();
    
    foreach ($services as $serviceName => $service) {
        if (!empty($service['api_key'])) {
            // Check the appropriate flag based on operation type
            $isEnabled = $isUpdate ? $service['on_update'] : $service['is_active'];
            
            if ($isEnabled) {
                $response = call_user_func($service['execute'], $productUrl, $service['api_key']);
                if ($response) return $response;
            }
        }
    }
    return false;
}


/**
 * Execute the Zyte Proxy API service.
 * 
 * @param string $productUrl
 * @param string $apiKey
 * @return string|boolean
 */
function executeZyteAPI($productUrl, $apiKey) {
    // Proxy endpoint as per Zyte documentation
    $proxyUrl = 'api.zyte.com:8011';
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $productUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxyUrl);
    
    // Set proxy credentials using the API key (username with no password)
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $apiKey . ':');
    
    // Set user agent for better results
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    // Execute cURL request
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    return $curl_errno ? false : $response;
}


/**
 * Execute the ScraperAPI service.
 * 
 * @param string $productUrl
 * @param string $apiKey
 * @return string|boolean
 */
function executeScraperAPI($productUrl, $apiKey) {
    $endpoint = 'http://api.scraperapi.com/?api_key=' . $apiKey . '&url=' . urlencode($productUrl);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    return $curl_errno ? false : $response;
}



/**
 * Execute the SCRAPINGANT API service.
 * 
 * @param string $productUrl
 * @param string $apiKey
 * @return string|boolean
 */
function executeScrapingAntAPI($productUrl, $apiKey) {
    $endpoint = 'https://api.scrapingant.com/v2/general?x-api-key=' . $apiKey . '&url=' . urlencode($productUrl) . '&browser=false';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    return $curl_errno ? false : $response;
}



/**
 * Scrapingant API credits count
 * 
 * @return array|false
 */
if (!function_exists('scrapingant_api_credits_status')) {
   function scrapingant_api_credits_status() {
       // Check if ScrapingAnt key exists and either mode is enabled
       $api_key = get_option('ams_scrapingant_api_key');
       $is_active = get_option('ams_scrapingant_is_active') == '1';
       $on_update = get_option('ams_scrapingant_on_update') == '1';
       
       if (!$api_key || (!$is_active && !$on_update)) {
           return false;
       }

       // Set endpoint for API credits status
       $endpoint = 'https://api.scrapingant.com/v2/usage?x-api-key=' . $api_key;

       // Initialize cURL 
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $endpoint);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
       curl_setopt($ch, CURLOPT_HTTPGET, true);

       $response = curl_exec($ch);
       
       if (curl_errno($ch)) {
           curl_close($ch);
           return false;
       }

       curl_close($ch);
       $response_data = json_decode($response, true);
       return $response_data ?: false;
   }
}


/**
 * ScraperAPI credits count
 * 
 * @return array|false
 */

if (!function_exists('ams_scraper_api_credits_count')) {
   function ams_scraper_api_credits_count() {
       // Check if ScraperAPI key exists and either mode is enabled
       $scraper_api_key = get_option('ams_scraper_api_key');
       $is_active = get_option('ams_scraper_api_is_active') == '1';
       $on_update = get_option('ams_scraper_api_on_update') == '1';
       
       if (!$scraper_api_key || (!$is_active && !$on_update)) {
           return false;
       }

       // ScraperAPI endpoint
       $endpoint = 'http://api.scraperapi.com/account?api_key=' . $scraper_api_key;
       
       // Create cURL handle
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $endpoint);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       
       // Make the request
       $response = curl_exec($ch);
       $curl_errno = curl_errno($ch);
       curl_close($ch);

       if ($curl_errno) {
           return false;
       }

       return json_decode($response, true);
   }
}


/**
 * ScraperAPI test code
 * 
 * @return array
 */
add_action('wp_ajax_scraper_api_test_code', 'scraper_api_test_code');
function scraper_api_test_code() {
    // Use a lighter URL for testing
    $test_url = 'https://httpbin.org/ip';
    
    // Get the ScraperAPI key
    $api_key = get_option('ams_scraper_api_key');
    
    if (empty($api_key)) {
        wp_send_json(array(
            'status' => false,
            'message' => '<small>ScraperAPI key is missing. Please enter your API key.</small>'
        ));
    }

    // Construct the ScraperAPI URL
    $scraper_url = "http://api.scraperapi.com?api_key={$api_key}&url=" . urlencode($test_url);

    // Set up the arguments for wp_remote_get
    $args = array(
        'timeout' => 10, // Set a shorter timeout
        'httpversion' => '1.1',
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
    );

    // Make the request
    $response = wp_remote_get($scraper_url, $args);

    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json(array(
            'status' => false,
            'message' => '<small>ScraperAPI request failed: ' . esc_html($response->get_error_message()) . '</small>'
        ));
    }

    // Check the response code
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        wp_send_json(array(
            'status' => false,
            'message' => '<small>ScraperAPI returned an unexpected response code: ' . esc_html($response_code) . '</small>'
        ));
    }

    // If we've made it this far, the API is working
    wp_send_json(array(
        'status' => true,
        'message' => '<small>ScraperAPI is working correctly!</small>'
    ));
}
/**/


/**
 * ScrapingAnt test code
 * 
 * @return array
 */
add_action('wp_ajax_scrapingant_test_code', 'scrapingant_test_code');
function scrapingant_test_code() {
    // Use a lighter URL for testing
    $test_url = 'https://httpbin.org/ip';
    
    // Get the ScrapingAnt API key
    $api_key = get_option('ams_scrapingant_api_key');
    
    if (empty($api_key)) {
        wp_send_json(array(
            'status' => false,
            'message' => '<small>ScrapingAnt API key is missing. Please enter your API key.</small>'
        ));
    }

    // Construct the ScrapingAnt API request
    $api_endpoint = 'https://api.scrapingant.com/v2/general';
    $request_url = add_query_arg('url', urlencode($test_url), $api_endpoint);

    // Set up the arguments for wp_remote_get
    $args = array(
        'timeout' => 30,
        'httpversion' => '1.1',
        'headers' => array(
            'x-api-key' => $api_key,
        ),
    );

    // Make the request
    $response = wp_remote_get($request_url, $args);

    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json(array(
            'status' => false,
            'message' => '<small>ScrapingAnt request failed: ' . esc_html($response->get_error_message()) . '</small>'
        ));
    }

    // Check the response code
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = wp_remote_retrieve_body($response);
        wp_send_json(array(
            'status' => false,
            'message' => '<small>ScrapingAnt returned an unexpected response code: ' . esc_html($response_code) . '. Error: ' . esc_html($error_message) . '</small>'
        ));
    }

    // If we've made it this far, the API is working
    wp_send_json(array(
        'status' => true,
        'message' => '<small>ScrapingAnt is working correctly!</small>'
    ));
}

function displayServerSettings() {
    $requirements = [
        'PHP Version' => '7.4',
        'Memory Limit' => 256,  // in MB
        'Upload Max Filesize' => 128,  // in MB
        'Max Input Time' => 120,  // in seconds
        'Post Max Size' => 128,  // in MB
        'Max Execution Time' => 600  // in seconds
    ];
    $settings = [
        __('PHP Version', 'ams-wc-amazon') => phpversion(),
        __('Memory Limit', 'ams-wc-amazon') => ini_get('memory_limit'),
        __('Upload Max Filesize', 'ams-wc-amazon') => ini_get('upload_max_filesize'),
        __('Max Input Time', 'ams-wc-amazon') => ini_get('max_input_time') . 's',
        __('Post Max Size', 'ams-wc-amazon') => ini_get('post_max_size'),
        __('Max Execution Time', 'ams-wc-amazon') => ini_get('max_execution_time') . 's'
    ];
    $iconPass = 'fas fa-check-circle';
    $iconFail = 'fas fa-times-circle';
    $formattedSettings = [];
    foreach ($settings as $key => $value) {
        $requirementKey = str_replace(__(' ', 'ams-wc-amazon'), ' ', $key);
        $requirement = $requirements[$requirementKey];
        if ($key === __('PHP Version', 'ams-wc-amazon')) {
            $result = version_compare($value, $requirement, '>=');
        } elseif (in_array($key, [__('Memory Limit', 'ams-wc-amazon'), __('Upload Max Filesize', 'ams-wc-amazon'), __('Post Max Size', 'ams-wc-amazon')])) {
            $currentValueMB = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            $result = ($currentValueMB >= $requirement);
        } else {
            $currentValueSec = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            $result = ($currentValueSec >= $requirement);
        }
        $formattedSettings[] = [
            'name' => $key,
            'value' => $value,
            'required' => $requirement . (in_array($key, [__('Memory Limit', 'ams-wc-amazon'), __('Upload Max Filesize', 'ams-wc-amazon'), __('Post Max Size', 'ams-wc-amazon')]) ? 'M' : ''),
            'icon' => $result ? $iconPass : $iconFail,
            'meets_requirement' => $result
        ];
    }
    return $formattedSettings;
}


function checkRequirement($setting, $currentValue, $requirements) {
    $requirement = $requirements[$setting];
    $iconPass = '<i class="fas fa-check-circle text-success"></i>';
    $iconFail = '<i class="fas fa-times-circle text-danger"></i>';
    
    if ($setting == __('PHP Version', 'ams-wc-amazon')) {
        $result = version_compare($currentValue, $requirement, '>=');
    } elseif (in_array($setting, [__('Memory Limit', 'ams-wc-amazon'), __('Upload Max Filesize', 'ams-wc-amazon'), __('Post Max Size', 'ams-wc-amazon')])) {
        $currentValueMB = (int) filter_var($currentValue, FILTER_SANITIZE_NUMBER_INT);
        $result = ($currentValueMB >= $requirement);
    } else {
        $currentValueSec = (int) filter_var($currentValue, FILTER_SANITIZE_NUMBER_INT);
        $result = ($currentValueSec >= $requirement);
    }
    
    return $result ? $iconPass : $iconFail;
}


/**
 * Fiu - recursive sanitization function
 */
function amswoofiu_sanitize( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'amswoofiu_sanitize', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( wp_unslash( $var ) ) : $var;
    }
}

/**
 * This Fiu function is used to either save the featured image url 
 * Or gallary images images url
 * 
 * @param int $post_id
 * @param array $data
 * 
 */
function amswoofiu_save_image_url_data( $post_id, $data = array() ) {
    if( isset( $data['amswoofiu_url'] ) ){
        // Update Featured Image URL
        $image_url = isset( $data['amswoofiu_url'] ) ? esc_url_raw( $data['amswoofiu_url'] ) : '';
        $image_alt = isset( $data['amswoofiu_alt'] ) ? wp_strip_all_tags( $data['amswoofiu_alt'] ): ''; 

        if ( $image_url != '' ){
            if( get_post_type( $post_id ) == 'product' ){
                $img_url = get_post_meta( $post_id, AMSWOOFIU_URL , true );
                if( is_array( $img_url ) && isset( $img_url['img_url'] ) && $image_url == $img_url['img_url'] ){
                        $image_url = array(
                            'img_url' => $image_url,
                            'width'   => $img_url['width'],
                            'height'  => $img_url['height']
                        );
                }else{
                    $imagesize = @getimagesize( $image_url );
                    $image_url = array(
                        'img_url' => $image_url,
                        'width'   => isset( $imagesize[0] ) ? $imagesize[0] : '',
                        'height'  => isset( $imagesize[1] ) ? $imagesize[1] : ''
                    );
                }
            }

            update_post_meta( $post_id, AMSWOOFIU_URL, $image_url );
            if( $image_alt ){
                update_post_meta( $post_id, AMSWOOFIU_ALT, $image_alt );
            }
        }else{
            delete_post_meta( $post_id, AMSWOOFIU_URL );
            delete_post_meta( $post_id, AMSWOOFIU_ALT );
        }
    }

    if( isset( $data['amswoofiu_product_gallary'] ) ){
        // Update WC Gallery
        $amswoofiu_wcgallary = isset( $data['amswoofiu_product_gallary'] ) ? amswoofiu_sanitize( $data['amswoofiu_product_gallary'] ) : '';
        if( empty( $amswoofiu_wcgallary ) || get_post_type( $post_id ) != 'product' ){
            return;
        }

        $old_images = amswoofiu_get_wcgallary_meta( $post_id );
        if( !empty( $old_images ) ){
            foreach ($old_images as $key => $value) {
                $old_images[$value['url']] = $value;
            }
        }

        $gallary_images = array();
        if( !empty( $amswoofiu_wcgallary ) ){
            foreach ($amswoofiu_wcgallary as $amswoofiu_gallary ) {
                if( isset( $amswoofiu_gallary['url'] ) && $amswoofiu_gallary['url'] != '' ){
                    $gallary_image = array();
                    $gallary_image['url'] = $amswoofiu_gallary['url'];

                    if( isset( $old_images[$gallary_image['url']]['width'] ) && $old_images[$gallary_image['url']]['width'] != '' ){
                        $gallary_image['width'] = isset( $old_images[$gallary_image['url']]['width'] ) ? $old_images[$gallary_image['url']]['width'] : '';
                        $gallary_image['height'] = isset( $old_images[$gallary_image['url']]['height'] ) ? $old_images[$gallary_image['url']]['height'] : '';

                    }else{
                        $imagesizes = @getimagesize( $amswoofiu_gallary['url'] );
                        $gallary_image['width'] = isset( $imagesizes[0] ) ? $imagesizes[0] : '';
                        $gallary_image['height'] = isset( $imagesizes[1] ) ? $imagesizes[1] : '';
                    }

                    $gallary_images[] = $gallary_image;
                }
            }
        }

        if( !empty( $gallary_images ) ){
            update_post_meta( $post_id, AMSWOOFIU_WCGALLARY, $gallary_images );
        }else{
            delete_post_meta( $post_id, AMSWOOFIU_WCGALLARY );
        }
    }
}

/**
 * This Fiu function is used to get the gallary images url
 * 
 * @param int $post_id
 * 
 */
function amswoofiu_get_wcgallary_meta( $post_id ) {
        
    $image_meta  = array();

    $gallary_images = get_post_meta( $post_id, AMSWOOFIU_WCGALLARY, true );
    
    if( !is_array( $gallary_images ) && $gallary_images != '' ){
        $gallary_images = explode( ',', $gallary_images );
        if( !empty( $gallary_images ) ){
            $gallarys = array();
            foreach ($gallary_images as $gallary_image ) {
                $gallary = array();
                $gallary['url'] = $gallary_image;
                $imagesizes = @getimagesize( $gallary_image );
                $gallary['width'] = isset( $imagesizes[0] ) ? $imagesizes[0] : '';
                $gallary['height'] = isset( $imagesizes[1] ) ? $imagesizes[1] : '';
                $gallarys[] = $gallary;
            }
            $gallary_images = $gallarys;
            update_post_meta( $post_id, AMSWOOFIU_WCGALLARY, $gallary_images );
            return $gallary_images;
        }
    }else{
        if( !empty( $gallary_images ) ){
            $need_update = false;
            foreach ($gallary_images as $key => $gallary_image ) {
                if( !isset( $gallary_image['width'] ) && isset( $gallary_image['url'] ) ){
                    $imagesizes1 = @getimagesize( $gallary_image['url'] );
                    $gallary_images[$key]['width'] = isset( $imagesizes1[0] ) ? $imagesizes1[0] : '';
                    $gallary_images[$key]['height'] = isset( $imagesizes1[1] ) ? $imagesizes1[1] : '';
                    $need_update = true;
                }
            }
            if( $need_update ){
                update_post_meta( $post_id, AMSWOOFIU_WCGALLARY, $gallary_images );
            }
            return $gallary_images;
        }   
    }
    return $gallary_images;
}

/**
 * This Fiu function is used to save the variation image url
 * 
 * @param int $variation_id
 * @param array $data
 * 
 */
function amswoofiu_save_product_variation_image( $variation_id, $data = array() ) {
    $image_url = isset( $data['amswoofiu_variation_url'][$variation_id] ) ? esc_url_raw( $data['amswoofiu_variation_url'][$variation_id] ) : '';
    if( $image_url != '' ){
        $img_url = get_post_meta( $variation_id, AMSWOOFIU_URL, true );
        if( is_array( $img_url ) && isset( $img_url['img_url'] ) && $image_url == $img_url['img_url'] ){
                $image_url = array(
                    'img_url' => $image_url,
                    'width'   => $img_url['width'],
                    'height'  => $img_url['height']
                );
        }else{
            $imagesize = @getimagesize( $image_url );
            $image_url = array(
                'img_url' => $image_url,
                'width'   => isset( $imagesize[0] ) ? $imagesize[0] : '',
                'height'  => isset( $imagesize[1] ) ? $imagesize[1] : ''
            );
        }
        update_post_meta( $variation_id, AMSWOOFIU_URL, $image_url );
    }else{
        delete_post_meta( $variation_id, AMSWOOFIU_URL );
    }
}

/**
 * Get country code part from URL.
 * 
 * @param string $product_url
 *
 * @return string
 */
function ams_get_country_from_product_url( $product_url ) {
    if( !$product_url ) return null;
    $host = parse_url( $product_url, PHP_URL_HOST );
    $exploded = explode(".", $host);
    return end( $exploded );
}

/**
 * Add filter to `wc_price_args` filter hook.
 * 
 * @param array $args price args.
 *
 * @return array
 */
function ams_wc_price_args_filter( $args ) {
    // Add conditional check
    // This hook work only for product pages 
    $product_url = get_post_meta( get_the_ID(), '_detail_page_url', true );

    if( $product_url ) {
        $currency = ams_get_country_from_product_url( $product_url );
        $currency_code = get_ams_woocommerce_currency_code($currency);
        if( !is_null( $currency_code ) ) {
            $args['currency'] = $currency_code;
        }
    }

    return $args;
}

add_filter( 'wc_price_args', 'ams_wc_price_args_filter' );

/**
 * Price dynamic hooks
 */
add_filter('woocommerce_product_get_price', 'ams_add_dropship_fee_to__price', 90, 2);
add_filter('woocommerce_product_get_regular_price', 'ams_add_dropship_fee_to__price', 90, 2);
add_filter('woocommerce_product_variation_get_regular_price', 'ams_add_dropship_fee_to__price', 99, 2);
add_filter('woocommerce_product_variation_get_price', 'ams_add_dropship_fee_to__price' , 99, 2);
add_filter('woocommerce_variation_prices_price', 'ams_add_dropship_fee_to__variation_price', 99, 3);
add_filter('woocommerce_variation_prices_regular_price', 'ams_add_dropship_fee_to__variation_price', 99, 3);

// Update the price of woocommerce products dynamically
function ams_add_dropship_fee_to_product_prices( $price, $product) {
    $product_ID = $product->get_id();
    if("variation" == $product->get_type()) {
        $product_ID = $product->get_parent_id();
    }

    if(!get_post_meta( $product_ID, '_ams_product_url', true)) {
        return $price;
    }

    $profit = 0;
    $price = (float) $price;
    if(strtolower(get_option('ams_buy_action_btn')) === strtolower('dropship')) {
        $percentage_profit = (float) get_option('ams_percentage_profit');
        $profit = $price * ($percentage_profit / 100);
    }

    return $price + $profit;
}

// Add dropship fee to price
function ams_add_dropship_fee_to__price( $price, $product ) {
    global $pagenow;
    if (!$product->is_in_stock()) return $price;
    if( ( $pagenow == 'edit.php' && $_GET['post_type'] == 'product') && ( current_user_can('editor') || current_user_can('administrator') ) ) return $price;
    wc_delete_product_transients($product->get_id());
    return ams_add_dropship_fee_to_product_prices( $price, $product );
}

// Add dropship fee to variation price
function ams_add_dropship_fee_to__variation_price( $price, $variation, $product ) { 
    global $pagenow;
    if( !$variation->get_price() ) return $price;
    if (!$variation->is_in_stock()) return $price;
    if( ( $pagenow == 'edit.php' && $_GET['post_type'] == 'product') && ( current_user_can('editor') || current_user_can('administrator') ) ) return $price;
    wc_delete_product_transients($variation->get_id());
    return ams_add_dropship_fee_to_product_prices( $price, $product );  
}

// add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );
function add_custom_price( $cart_object ) {
    foreach ( $cart_object->cart_contents as $key => $value ) {
        $product = $value['data'];
        $product_ID = $product->get_id();
        if( "variation" == $product->get_type() ) {
            $product_ID = $product->get_parent_id();
        }

        if( ! get_post_meta( $product_ID, '_ams_product_url', true) ) {
            return;
        }

        $profit = 0;
        $price = (float) $product->get_price();
        if( strtolower(get_option('ams_buy_action_btn')) === strtolower('dropship') ) {
            $percentage_profit = (float) get_option('ams_percentage_profit');
            $profit = $price * ($percentage_profit / 100);
        }
        
        // for WooCommerce version 3+ use: 
        $product->set_price( $price + $profit );
    }
}

function getAlternatingBool() {
    static $state = false;
    $state = !$state;
    return $state;
}

/**
 * Import Product Faster
 */
function advancedProductImporter($productData) {
    if (count($productData['attributes']) > 0) {
        // Create a new instance of WC_Product_Variable
        $product = new \WC_Product_Variable();
    } else {
        // Create a new instance of WC_Product_Simple
        $product = new \WC_Product_Simple();
    }
    // Get status settings
    $importStatus = get_option('ams_product_import_status', true);
    // Set the product data
    $product->set_name(stripslashes($productData['title']));
    $product->set_status($importStatus);
    // Save the new product
    $product->save();
    $post_id = $product->get_id();

    // Check if product type is simple
    if ($product->is_type('simple')) {
        $product->set_regular_price($productData['regular_price']);
        if (!empty($productData['sale_price']) && $productData['sale_price'] < $productData['regular_price']) {
            $product->set_sale_price($productData['sale_price']);
            $product->set_price($productData['sale_price']);
        } else {
            $product->set_price($productData['regular_price']);
        }
        $product->set_sku($productData['asin']);
    } else {
        $product->set_sku($productData['parentSku']);
    }

    $product->set_catalog_visibility('visible');
    $product->set_sold_individually(false);
    $product->set_backorders('no');
    
    update_post_meta($post_id, 'total_sales', '0');
    update_post_meta($post_id, '_downloadable', 'no');
    update_post_meta($post_id, '_purchase_note', '');
    update_post_meta($post_id, '_featured', 'no');
    update_post_meta($post_id, '_weight', '');
    update_post_meta($post_id, '_length', '');
    update_post_meta($post_id, '_width', '');
    update_post_meta($post_id, '_height', '');
    update_post_meta($post_id, '_wca_amazon_affiliate_asin', $productData['asin']);
    update_post_meta($post_id, '_wca_amazon_affiliate_parent_asin', $productData['parentSku']);
    update_post_meta($post_id, '_region', $productData['region']);
    update_post_meta($post_id, '_import_method', $productData['import_method']);
    update_post_meta($post_id, '_ams_product_url', $productData['product_url']);
    update_post_meta($post_id, '_detail_page_url', $productData['product_url']);
    update_post_meta($post_id, 'ams_last_cron_update', date('Y-m-d H:i:s'));
    update_post_meta($post_id, 'ams_last_cron_status', 0);

    $product->save();

    if (!is_null($productData['default_message'])) {
        $message = esc_html($productData['default_message']);
    } else {
        $message = sprintf(
            '%s <a href="%s" target="_blank" class="text-white">%s</a>',
            esc_html__('Product import Successfully. ---', 'ams-wc-amazon'),
            esc_url($productData['product_url']),
            esc_html(wp_str_limit($productData['product_url'], 25, '...'))
        );
    }
    echo wp_kses_post($message);
}

/**
 * Checkout Redirect
 */
add_action('init', 'ams_checkout_rewrite_rules');
function ams_checkout_rewrite_rules() {
    add_rewrite_rule('^ams_redirect/?$', 'index.php?ams_redirect=1', 'top');
    flush_rewrite_rules();
}

add_filter('query_vars', 'ams_checkout_query_vars');
function ams_checkout_query_vars( $query_vars ) {
    $query_vars[] = 'ams_redirect';
    $query_vars[] = 'ams_redirect_uri';
    return $query_vars;
}

add_action('template_redirect', 'ams_checkout_template_redirect');
function ams_checkout_template_redirect() {
    global $wp_query;
    if (get_query_var('ams_redirect')) {
        $redirect_url = esc_url_raw(get_query_var('ams_redirect_uri'));
        if ($redirect_url) {
            // Output the redirect page with countdown
            $interval = get_option( 'ams_checkout_redirected_seconds', true ) ?? 3;
            do_action( 'ams_checkout_redirect_page', $redirect_url, $interval );
            exit;
        }
    }
}

add_action('ams_checkout_redirect_page', 'ams_checkout_redirect_page_html', 10, 2);
function ams_checkout_redirect_page_html( $redirect_url, $interval ) {
    $redirect_file_path = AMS_PLUGIN_PATH . 'templates/template-redirect.php';
    if( file_exists( $redirect_file_path ) ) {
        require_once $redirect_file_path;
    }
}

/**
 * CRON Activation
 */
function ams_custom_cron_activation() {
    if ( !wp_next_scheduled( 'ams_product_availability' ) ) {
        wp_schedule_event( time(), 'ams_every_day', 'ams_product_availability');
    }
}
add_action('wp', 'ams_custom_cron_activation');

/**
 * CRON Action HOOK
 */
add_action( 'ams_product_availability', 'ams_product_availability' );



#############################################################

    function fetchAndValidateProductData($product_url, $user_agent, $force_scraping_service = false) {
        $max_attempts = 3;
        $attempt = 0;

        while ($attempt < $max_attempts) {
            $attempt++;
            //logImportVerification("Attempt $attempt of $max_attempts", null);

            sleep(rand(2, 3));
            $response_body = null;
            $response_code = 0;

            if (!$force_scraping_service) {
                $ch = curl_init($product_url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_CONNECTTIMEOUT => 120,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_USERAGENT => $user_agent,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_ENCODING => ""
                ]);
                $response_body = curl_exec($ch);
                $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($response_code == 200 && stripos($response_body, 'captcha') === false && stripos($response_body, 'productTitle') !== false) {
                    return $response_body;
                }
            }

            logImportVerification('Using scraping service', null);
            $scraping_response = executeScrapingService($product_url);
            
            if ($scraping_response === false) {
                logImportVerification('Scraping service failed', null);
                if ($attempt == $max_attempts) {
                    echo esc_html__('Error accessing the product. Enable proxy and try again.', 'ams-wc-amazon');
                    wp_die();
                }
                continue; // Try again
            }
            
            $response_body = is_array($scraping_response) ? $scraping_response['data'] : $scraping_response;
            
            if (stripos($response_body, 'productTitle') !== false) {
                logImportVerification('Scraping service used successfully', null);
                return $response_body;
            }

            logImportVerification('Failed to retrieve product title even with scraping service', null);
            
            if ($attempt == $max_attempts) {
                echo wp_kses_post(esc_html__('Unable to retrieve product information. Please try again later.', 'ams-wc-amazon'));
                wp_die();
            }
            // If we reach here, we'll try again
        }
    }

    function check_product_stock_status($html) {
        $outOfStockIndicators = [
            // Classes and IDs
            '#outOfStock .a-color-price',
            '.out-of-stock',
            '.outOfStock',
            '.stockStatus-unavailable',
            '.noInventory',
            '#outOfStock',
            '.sold-out',
            '.stock-status-out',

            // Phrases (case-insensitive)
            'temporarily out of stock',
            'currently unavailable',
            'derzeit nicht verfügbar',
            'vorübergehend nicht auf lager',
            'temporairement en rupture de stock',
            'actuellement indisponible',
            'temporalmente agotado',
            'actualmente no disponible',
            'temporaneamente non disponibile',
            'attualmente non disponibile',
            'temporariamente sem estoque',
            'atualmente indisponível',
            '暂时缺货',
            '目前无法使用',
            '在庫切れ',
            '現在お取り扱いできません',
            'tijdelijk niet op voorraad',
            'momenteel niet beschikbaar',
            'Ikke til rådighed i øjeblikket.',
            'غير متوفر مؤقتًا',
            'غير متوفر حاليًا',
            'geçici olarak stokta yok',
            'şu anda mevcut değil',
            'chwilowo niedostępny',
            'obecnie niedostępne',
            'Agotado temporalmente',
            'No disponible'
        ];

        // Function to find the product detail div
        $findProductDiv = function($html) {
            $attempts = ['div#ppd', 'div[id^="ppd"]', 'div[id*="ppd"]'];
            foreach ($attempts as $attempt) {
                $div = $html->find($attempt, 0);
                if ($div) return $div;
            }
            return $html; // If not found, use the entire HTML
        };

        $productDiv = $findProductDiv($html);

        // Check for out-of-stock indicators
        foreach ($outOfStockIndicators as $indicator) {
            if (strpos($indicator, '.') === 0 || strpos($indicator, '#') === 0) {
                // It's a class or ID selector
                if ($productDiv->find($indicator, 0)) {
                    return 'outofstock';
                }
            } else {
                // It's a phrase
                if (stripos($productDiv->plaintext, $indicator) !== false) {
                    return 'outofstock';
                }
            }
        }

        // If no out-of-stock indicator found, assume it's in stock
        return 'instock';
    }

    function getFirstRangedSkus($skus, $min=15, $max=20, $shuffle=true, $debug=false) {
        if (!is_array($skus) || empty($skus)) {
            if ($debug) error_log('getFirstRangedSkus: Input is not an array or is empty');
            return [];
        }

        $skus = array_values(array_unique($skus));
        $totalSkus = count($skus);

        $max = min($max, $totalSkus);
        $min = min($min, $max);

        if ($shuffle) {
            shuffle($skus);
        }

        $count = rand($min, $max);
        $result = array_slice($skus, 0, $count);

        if ($debug) {
            error_log('getFirstRangedSkus Debug: ' . json_encode([
                'initial_count' => $totalSkus,
                'adjusted_min' => $min,
                'adjusted_max' => $max,
                'selected_count' => $count,
                'result_count' => count($result)
            ]));
        }

        return $result;
    }

    function logImportVerification($identifier, $data = null, $newCycle = false, $endCycle = false) {
        static $isFirstEntry = true;
        
        $log_file = plugin_dir_path(__FILE__) . 'import_verification.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $log_entry = "";
        
        if ($newCycle && $isFirstEntry) {
            $log_entry .= str_repeat("=", 80) . "\n";
            $isFirstEntry = false;
        }
        
        $log_entry .= "$timestamp: $identifier";
        
        if (is_array($data) || is_object($data)) {
            $log_entry .= " - " . json_encode($data, JSON_UNESCAPED_UNICODE);
        } elseif ($data !== null) {
            $log_entry .= $data;
        }
        
        $log_entry .= "\n";
        
        if ($endCycle) {
            $log_entry .= str_repeat("=", 80) . "\n\n";
            $isFirstEntry = true;
        }
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }

    function ams_clear_import_logs() {
        $logs_page = new \Amazon\Affiliate\Admin\LogsPage();
        $result = $logs_page->clear_logs();
        
        if ($result) {
            // Log the clearing action
            logImportVerification('Logs Cleared', 'Logs were cleared programmatically', true);
            return true;
        } else {
            // Log the failed attempt
            logImportVerification('Logs Clearing Failed', 'Attempt to clear logs programmatically failed', true);
            return false;
        }
    }


function validate_amazon_page($content, $loop_html, $base_url, $http_status_code) {
    // 1. If status code isn't 200, treat as invalid.
    if ($http_status_code !== 200) {
        return "Invalid page: HTTP code {$http_status_code}";
    }

    // 2. Check for typical Amazon 404 text.
    $possible404Texts = [
        "Looking for something?",
        "The Web address you entered is not a functioning page on our site",
        "Page Not Found"
    ];
    foreach ($possible404Texts as $text) {
        if (stripos($response_body, $text) !== false) {
            return "Invalid page: found '{$text}' in the response.";
        }
    }

    // 3. Check for essential Amazon product elements.
    $hasTitle = $html->find('#productTitle, .product-title, h1.a-size-large', 0);
    $hasImage = $html->find('#imgBlkFront, #landingImage, [data-component-type="s-product-image"], .a-image-container', 0);
    $hasPrice = $html->find('.a-price, .a-color-price, #priceblock_ourprice, #priceblock_dealprice', 0);

    if (!$hasTitle || !$hasImage || !$hasPrice) {
        return "Invalid page: essential elements missing.";
    }

    // If valid, print info and return "Valid".
    $titleElement = $html->find('title', 0);
    $page_title = $titleElement ? trim($titleElement->plaintext) : 'No Title Found';
    
    echo "Valid page.<br>";
    echo "Page Title: {$page_title}<br>";
    echo "Base URL: {$base_url}<br>";

    return "Valid page.";
}









    function check_for_broken_page($response_body, $html) {
        // Check for definite error patterns
        $definiteErrorPatterns = [
            '/This site can\'t be reached/i',
            '/DNS_PROBE_FINISHED_NXDOMAIN/i',
            '/ERR_NAME_NOT_RESOLVED/i',
            '/404 Not Found/i',
            '/Access Denied/i',
            '/Unable to connect/i',
            '/Connection timed out/i',
            '/Server not found/i',
            '/Page not available/i',
            '/This page isn\'t working/i',
            '/ERR_CONNECTION_REFUSED/i',
            '/ERR_EMPTY_RESPONSE/i',
            '/ERR_INVALID_RESPONSE/i',
            '/The connection was reset/i',
            '/No internet/i'
        ];

        foreach ($definiteErrorPatterns as $pattern) {
            if (preg_match($pattern, $response_body)) {
                $error_message = esc_html__('The product page appears to be inaccessible. Please check the URL and try again.', 'ams-wc-amazon');
                logImportVerification($error_message, null);
                return $error_message;
            }
        }

        // Check for essential elements with more flexibility
        $essentialElementSets = [
            ['#productTitle', '.product-title', 'h1.a-size-large'],  // Product title
            ['#imgBlkFront', '#landingImage', '[data-component-type="s-product-image"]', '.a-image-container'],  // Product image
            ['.a-price', '.a-color-price', '#priceblock_ourprice', '#priceblock_dealprice']  // Price
        ];

        $missingElementSets = 0;
        foreach ($essentialElementSets as $elementSet) {
            $found = false;
            foreach ($elementSet as $element) {
                if ($html->find($element, 0)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingElementSets++;
            }
        }

        // If most essential element sets are missing, consider the page potentially broken
        if ($missingElementSets >= 2) {
            // Additional check: Look for any typical Amazon product page elements
            $amazonSpecificElements = [
                '#nav-main', // Amazon navigation
                '#navbar',   // Another navigation identifier
                '#footer',   // Footer
                '#dp',       // Common product page container
                '.a-box'     // Common Amazon UI element
            ];

            $amazonElementsFound = 0;
            foreach ($amazonSpecificElements as $element) {
                if ($html->find($element, 0)) {
                    $amazonElementsFound++;
                }
            }

            // If we find at least two Amazon-specific elements, give the benefit of the doubt
            if ($amazonElementsFound < 2) {
                $error_message = esc_html__('The product page seems to be missing crucial elements. Please verify the URL and try again.', 'ams-wc-amazon');
                logImportVerification($error_message, null);
                return $error_message;
            }
        }

        // Page seems to be valid
        return null;
    }

    // Extracts the product title
    function extractAmazonProductTitle($html) {
        $selectors = [
            '#productTitle',
            '#bondTitle_feature_div',
            '.product-title-word-break',
            '.a-size-large.product-title-word-break',
            'h1.a-size-large',
            '#title',
            '.qa-title-text',
            '[data-feature-name="title"]'
        ];
        
        foreach ($selectors as $selector) {
            if ($title = $html->find($selector, 0)) {
                return trim($title->plaintext);
            }
            //echo '<pre>'; print_r('title: '. $title); echo '</pre>'; exit;
        }
        
        $message = esc_html__('Unable to extract product title. Import process stopped.', 'ams-wc-amazon');
        echo wp_kses_post($message);
        return false;
    }

        /*shorten_product_title*/
        function shorten_product_title_backend($title, $id = null) {
            if (!is_admin() || !$id) {
                return $title;
            }
            
            $product = wc_get_product($id);
            // Only modify titles for products
            if (!$product) {
                return $title;
            }
            
            $max_length = 50;
            $suffix = '...';
            if (strlen($title) > $max_length) {
                $title = substr($title, 0, $max_length - strlen($suffix)) . $suffix;
            }
            
            return $title;
        }

        // Apply the filter only in admin pages
        add_filter('the_title', 'shorten_product_title_backend', 10, 2);
        add_filter('woocommerce_product_title', 'shorten_product_title_backend', 10, 2);


    /*edit ams cron status title on products page*/
    add_filter('manage_edit-product_columns', 'wootix_show_product_order', 15);
    function wootix_show_product_order($columns) {
        // Add a new column for cron status
        $columns['cron_run_status'] = __('Cron Job', 'ams-wc-amazon');
        return $columns;
    }

    add_action('admin_head', 'wootix_custom_admin_css');
    function wootix_custom_admin_css() {
        echo '<style>
            .wp-list-table .column-cron_run_status {
                text-align: center;
                width: auto;
            }
            .wp-list-table th.column-cron_run_status {
                background: #f8f9fa;
                border-bottom: 2px solid #2196f3;
                font-weight: 500;
                color: #333;
            }
            .wp-list-table th.column-cron_run_status:hover {
                background: #e8f0fe;
            }
        </style>';
    }

    function check_sku_exists($product_url) {
        global $wpdb;
        
        $product_url = esc_url_raw($product_url);
        $parsed_url = parse_url($product_url);
        $path_parts = explode('/', trim($parsed_url['path'], '/'));
        $asin = end($path_parts);
        
        //error_log("Checking SKU/ASIN: " . $asin);
        
        if (empty($asin) || strlen($asin) != 10) {
            //error_log("Invalid ASIN extracted from URL: " . $product_url);
            return false;
        }
        
        // Check if ASIN exists and remove if orphaned
        $query = $wpdb->prepare(
            "DELETE pm FROM {$wpdb->postmeta} pm
             LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = '_sku' AND pm.meta_value = %s AND (p.ID IS NULL OR p.post_type != 'product')",
            $asin
        );
        $deleted = $wpdb->query($query);
        
        if ($deleted > 0) {
            //error_log("Removed orphaned SKU metadata: " . $deleted . " entries");
        }
        
        // Now check if the product actually exists
        $product_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_sku' AND meta_value = %s
             LIMIT 1",
            $asin
        ));
        
        if ($product_id) {
            $message = sprintf(esc_html__('This product (ASIN: %s) is already imported (Product ID: %d)', 'ams-wc-amazon'), $asin, $product_id);
            echo wp_kses_post($message);
            wp_die();
        }
        
        return false;
    }

    function check_sku_and_parent_sku($asin, $parent_sku) {
        global $wpdb;

        // Function to check if a SKU exists
        $check_sku = function($sku) use ($wpdb) {
            if (empty($sku) || strlen($sku) != 10) {
                return false;
            }

            // Remove orphaned entries
            $wpdb->query($wpdb->prepare(
                "DELETE pm FROM {$wpdb->postmeta} pm
                 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE pm.meta_key = '_sku' AND pm.meta_value = %s AND (p.ID IS NULL OR p.post_type != 'product')",
                $sku
            ));

            // Check if the product exists
            $product_id = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta}
                 WHERE meta_key = '_sku' AND meta_value = %s
                 LIMIT 1",
                $sku
            ));

            return $product_id ? $product_id : false;
        };

        // Check original ASIN
        $original_product_id = $check_sku($asin);
        if ($original_product_id) {
            $message = sprintf(esc_html__('This product (ASIN: %s) is already imported (Product ID: %d)', 'ams-wc-amazon'), $asin, $original_product_id);
            echo wp_kses_post($message);
            wp_die();
        }

        // Check parent SKU if it's different from the original ASIN
        if ($parent_sku && $parent_sku !== $asin) {
            $parent_product_id = $check_sku($parent_sku);
            if ($parent_product_id) {
                $message = sprintf(esc_html__('The parent product (ASIN: %s) is already imported (Product ID: %d)', 'ams-wc-amazon'), $parent_sku, $parent_product_id);
                echo wp_kses_post($message);
                wp_die();
            }
        }

        return false; // Neither original ASIN nor parent SKU found
    }

    function extractAsin($html, $product_url) {
        $asinElements = $html->find('#ASIN');
        $asin = !empty($asinElements) ? $asinElements[0]->value : '';
        
        if(empty($asin)) {
            $elements = $html->find('input[name="ASIN.0"]');
            $asin = !empty($elements) ? $elements[0]->value : '';
        }
        
        if(empty($asin)) {
            $urlArray = explode("dp/", $product_url);
            if (isset($urlArray[1])) {
                $urlArray = explode("/", $urlArray[1]);
                $asin = $urlArray[0];
            }
        }
        
        return $asin;
    }



    //For API//
    function attach_product_thumbnail_url_api($post_id, $url, $flag) {
        if (empty($url)) {
            error_log('Error: No URL provided for attach_product_thumbnail_url_api function.');
            return;
        }

        // Assign to featured image (flag = 0)
        if ($flag === 0) {
            $data = array('amswoofiu_url' => $url, 'amswoofiu_alt' => '');  // Prepare data array for external URL
            amswoofiu_save_image_url_data($post_id, $data);  // Save image URL data (custom system)
            update_post_meta($post_id, '_thumbnail_ext_url', esc_url($url)); // Only use _thumbnail_ext_url for external URL, avoid _thumbnail_id
        }

        // Assign to product gallery (flag = 1)
        if ($flag === 1) {
            $existing_gallery = get_post_meta($post_id, '_product_image_gallery', true);
            $gallery_urls = !empty($existing_gallery) ? explode(',', $existing_gallery) : [];

            // Handle both single URL string and array of URLs
            $urls_to_add = is_array($url) ? $url : array($url);

            foreach ($urls_to_add as $image_url) {
                if (!empty($image_url) && !in_array($image_url, $gallery_urls)) {
                    $gallery_urls[] = esc_url($image_url); // Add new URL to gallery
                }
            }

            // Save updated gallery
            $new_gallery = implode(',', array_filter($gallery_urls));
            update_post_meta($post_id, '_product_image_gallery', $new_gallery);

            // Save for compatibility with your existing system
            $product_gallery = array();
            foreach ($gallery_urls as $image_url) {
                if (!empty($image_url)) {
                    $product_gallery[] = array('url' => $image_url);
                }
            }
            $amswoofiu_product_gallery['amswoofiu_product_gallary'] = $product_gallery;
            amswoofiu_save_image_url_data($post_id, $amswoofiu_product_gallery);  // Save external URLs data for gallery images
        }

        // Assign to product variation image (flag = 2)
        if ($flag === 2) {
            $data = array('amswoofiu_variation_url' => array($post_id => $url));
            amswoofiu_save_product_variation_image($post_id, $data);  // Save variation image external URL
            update_post_meta($post_id, '_thumbnail_ext_url', esc_url($url)); // Only use _thumbnail_ext_url for external URL, avoid _thumbnail_id
        }

        return true; // Always return true to indicate successful operation
    }

    function attach_product_thumbnail_api($post_id, $url, $flag) {
        
        if (empty($url)) {
            error_log('Error: No URL provided for attach_product_thumbnail function.');
            return;
        }
        $image_url = $url;
        $url_array = explode('/', $url);
        $image_name = end($url_array);
        $result = wp_remote_get($image_url);

        // Check for WP Error
        if (is_wp_error($result)) {
            error_log('Error fetching image: ' . $result->get_error_message());
            return;
        }
    
        $image_data = wp_remote_retrieve_body($result);
        $upload_dir = wp_upload_dir(); // Set upload folder.
        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name.
        $filename = basename($unique_file_name); // Create image file name.
    
        // Define file location
        $file = (wp_mkdir_p($upload_dir['path'])) ? $upload_dir['path'] . '/' . $filename : $upload_dir['basedir'] . '/' . $filename;
    
        // Store the file
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        global $wp_filesystem;
        WP_Filesystem(); // Initialize WP file system
        $wp_filesystem->put_contents($file, $image_data, 0644); // Store the file
    
        // Check image file type
        $wp_filetype = wp_check_filetype($filename, null);
    
        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );
    
        // Create the attachment
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
    
        // Define attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    
        // Assign metadata to attachment
        wp_update_attachment_metadata($attach_id, $attach_data);
    
        // Assign to feature image
        if ($flag === 0) {
            // Check if the post exists and is a valid image
            if (get_post_status($post_id) !== false && wp_attachment_is_image($attach_id)) {
                // Set the featured image
                $set_thumbnail_result = set_post_thumbnail($post_id, $attach_id);
        
                if ( $set_thumbnail_result ) {
                    // If successfully set, optionally perform additional actions or log success
                    // For example: error_log("Featured image set successfully for post ID: $post_id");
                } else {
                    // Log error if failed to set the featured image
                    error_log("Failed to set featured image for post ID: $post_id with attachment ID: $attach_id");
                }
            } else {
                // Log error if post does not exist or attachment is not a valid image
                error_log("Invalid post ID or attachment ID for setting featured image. Post ID: $post_id, Attachment ID: $attach_id");
            }
        }
    
    
        // Assign to the product gallery
        if ($flag === 1) {
            $attach_id_array = get_post_meta($post_id, '_product_image_gallery', true);
            $attach_id_array = empty($attach_id_array) ? $attach_id : $attach_id_array . ',' . $attach_id;
            update_post_meta($post_id, '_product_image_gallery', $attach_id_array);
        }
    
        return $attach_id;
    }
    //For API//



    function attach_product_thumbnail_url($post_id, $url, $flag) {
        if (empty($url)) {
            return null; // Return null if no URL is provided
        }

        $result_url = null;

        // Assign to feature image
        if ($flag === 0) {
            $data = array('amswoofiu_url' => $url, 'amswoofiu_alt' => '');
            amswoofiu_save_image_url_data($post_id, $data);

            //print_r($data); exit;

            // Only use _thumbnail_ext_url for external URL, avoid _thumbnail_id
            update_post_meta($post_id, '_thumbnail_ext_url', $url);
            $result_url = $url; // Set the result to return the URL
        }

        // Assign to the product gallery
        if ($flag === 1) {
            $existing_gallery = get_post_meta($post_id, '_product_image_gallery', true);
            $gallery_urls = $existing_gallery ? explode(',', $existing_gallery) : array();
            
            // Handle both single URL string and array of URLs
            $urls_to_add = is_array($url) ? $url : array($url);
            
            foreach ($urls_to_add as $image_url) {
                if (!empty($image_url) && !in_array($image_url, $gallery_urls)) {
                    $gallery_urls[] = $image_url;
                }
            }
            
            // Ensure no empty values are added to the gallery
            $new_gallery = implode(',', array_filter($gallery_urls));

            update_post_meta($post_id, '_product_image_gallery', $new_gallery);

            // Save for compatibility with your existing system
            $product_gallery = array();
            foreach ($gallery_urls as $image_url) {
                if (!empty($image_url)) {
                    $product_gallery[] = array('url' => $image_url);
                }
            }
            $amswoofiu_product_gallery['amswoofiu_product_gallary'] = $product_gallery;
            amswoofiu_save_image_url_data($post_id, $amswoofiu_product_gallery);

            $result_url = $url; // Set the result to return the URL
        }

        // Assign to the product variation image
        if ($flag === 2) {
            $data = array(
                'amswoofiu_variation_url' => array($post_id => $url)
            );
            amswoofiu_save_product_variation_image($post_id, $data);
            // Only use _thumbnail_ext_url for external URL, avoid _thumbnail_id
            update_post_meta($post_id, '_thumbnail_ext_url', $url);
            $result_url = $url; // Set the result to return the URL
        }

        return $result_url;
    }

    function attach_product_thumbnail($post_id, $url, $flag) {
        if (empty($url)) {
            //error_log('Error: No URL provided for attach_product_thumbnail function.');
            return false;
        }

        $image_url = $url;
        $url_array = explode('/', $url);
        $image_name = end($url_array);
        $result = wp_remote_get($image_url);

        // Check for WP Error
        if (is_wp_error($result)) {
            //error_log('Error fetching image: ' . $result->get_error_message());
            return false;
        }

        $image_data = wp_remote_retrieve_body($result);
        $upload_dir = wp_upload_dir();
        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name);
        $filename = basename($unique_file_name);
        $file = $upload_dir['path'] . '/' . $filename;

        // Store the file
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        global $wp_filesystem;
        WP_Filesystem();
        if (!$wp_filesystem->put_contents($file, $image_data, 0644)) {
            //error_log('Error storing the file: ' . $file);
            return false;
        }

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);

        if ($flag === 0) {
            if (get_post_status($post_id) !== false && wp_attachment_is_image($attach_id)) {
                set_post_thumbnail($post_id, $attach_id);
            }
        }

        if ($flag === 1) {
            $attach_id_array = get_post_meta($post_id, '_product_image_gallery', true);
            $attach_id_array = empty($attach_id_array) ? $attach_id : $attach_id_array . ',' . $attach_id;
            update_post_meta($post_id, '_product_image_gallery', $attach_id_array);
        }

        return $attach_id;
    }

    function delete_product_images( $post_id ) {
        $product = wc_get_product( $post_id );
        if ( !$product ) {
            return;
        }

        // Get the current image ID and file path
        $image_id = $product->get_image_id();
        $file_path = get_attached_file($image_id);

        // Remove the image from the product
        $product->set_image_id(0);
        $product->save();

        // Delete the image file from the server
        if (file_exists($file_path)) {
            wp_delete_attachment($image_id, true);
        }
    }

    function delete_product_gallery_images( $post_id ) {
        $product = wc_get_product( $post_id );
        if ( !$product ) {
            return;
        }
        
        // Get the current gallery image IDs and file paths
        $gallery_image_paths = [];
        $gallery_image_ids = $product->get_gallery_image_ids();
        foreach ( $gallery_image_ids as $image_id ) {
            $gallery_image_paths[$image_id] = get_attached_file($image_id);
        }

        // Remove all images from the gallery
        $product->set_gallery_image_ids(array());
        $product->save();

        // Delete the image files from the server
        foreach ($gallery_image_ids as $image_id) {
            if (isset($gallery_image_paths[$image_id]) && file_exists($gallery_image_paths[$image_id])) {
                wp_delete_attachment($image_id, true);
            }
        }
    }

    function reset_product_thumbnail_url($post_id, $flag) {
        // Assign to feature image
        if ($flag === 0) {
            $data = array('amswoofiu_url' => '', 'amswoofiu_alt' => '');
            amswoofiu_save_image_url_data($post_id, $data);
        }
    
        // Assign to the product gallery
        if ($flag === 1) {
            $amswoofiu_product_gallary = ['amswoofiu_product_gallary'=>[]];
            amswoofiu_save_image_url_data($post_id, $amswoofiu_product_gallary);
        }

        // Assign to the product variation image
        if ($flag === 2) {
            $data = ['amswoofiu_variation_url' => []];
            amswoofiu_save_product_variation_image($post_id, $data);
        }
    }


function delete_local_product_images($post_id) {
    global $wpdb;
    
    // First, let's get ALL image attachments related to this product
    $all_attachment_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = 'attachment' 
        AND (post_parent = %d OR ID IN (
            SELECT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE post_id = %d 
            AND meta_key IN ('_thumbnail_id', '_product_image_gallery')
        ))",
        $post_id,
        $post_id
    ));

    $product = wc_get_product($post_id);
    if ($product && $product->is_type('variable')) {
        // Get all variation IDs
        $variation_ids = $product->get_children();
        
        // Get all variation image attachments
        if (!empty($variation_ids)) {
            $variation_ids_string = implode(',', array_map('absint', $variation_ids));
            $variation_attachments = $wpdb->get_col("
                SELECT meta_value 
                FROM {$wpdb->postmeta} 
                WHERE post_id IN ({$variation_ids_string})
                AND meta_key = '_thumbnail_id'
            ");
            
            if (!empty($variation_attachments)) {
                $all_attachment_ids = array_merge($all_attachment_ids, $variation_attachments);
            }
        }
    }

    // Remove duplicates and empty values
    $all_attachment_ids = array_filter(array_unique($all_attachment_ids));

    foreach ($all_attachment_ids as $attachment_id) {
        // Force delete all meta for this attachment
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d",
            $attachment_id
        ));

        // Get file path before deleting post
        $file_path = get_attached_file($attachment_id);
        
        // Delete the attachment post completely
        wp_delete_post($attachment_id, true);

        // Delete physical files
        if ($file_path && file_exists($file_path)) {
            // Get directory path
            $dir_path = dirname($file_path);
            
            // Get filename without extension
            $filename = pathinfo($file_path, PATHINFO_FILENAME);
            
            // Delete main file
            @unlink($file_path);
            
            // Delete all size variations
            $files = glob($dir_path . '/' . $filename . '-*');
            if ($files) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
        }
    }

    // Clean up ALL possible meta keys
    delete_post_meta($post_id, '_thumbnail_id');
    delete_post_meta($post_id, '_product_image_gallery');

    // Clean up variations
    if ($product && $product->is_type('variable')) {
        foreach ($product->get_children() as $variation_id) {
            delete_post_meta($variation_id, '_thumbnail_id');
        }
    }

    return count($all_attachment_ids);
}

function delete_product_image_urls($post_id) {
    // Get the variation meta key from options
    $variation_meta_key = get_option('variation_image_meta_key', '');
    
    $deleted_count = 0;
    
    // Array of meta keys to check and delete
    $meta_keys = array(
        '_thumbnail_id_url',
        '_amswoofiu_wcgallary',
        '_amswoofiu_url',
        '_thumbnail_ext_url',
        '_product_image_gallery'
    );
    
    // Add variation meta key if exists
    if (!empty($variation_meta_key)) {
        $meta_keys[] = $variation_meta_key;
    }
    
    // Only count if meta existed and was deleted
    foreach($meta_keys as $meta_key) {
        $meta_value = get_post_meta($post_id, $meta_key, true);
        if (!empty($meta_value)) {
            delete_post_meta($post_id, $meta_key);
            $deleted_count++;
        }
    }
    
    return $deleted_count;
}

add_action('wp_ajax_delete_amswoofiu_data_cleanup', 'handle_delete_amswoofiu_data_cleanup');
function handle_delete_amswoofiu_data_cleanup() {
    global $wpdb;
    
    // Get all product IDs including variations
    $product_ids = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type IN ('product', 'product_variation')
    ");
    
    $urls_deleted = 0;
    $images_deleted = 0;
    
    foreach($product_ids as $post_id) {
        // Get URL deletion count
        $urls_deleted += delete_product_image_urls($post_id);
        // Get image deletion count
        $images_deleted += delete_local_product_images($post_id);
    }
    
    // Fixed orphaned attachments cleanup query
    $wpdb->query("
        DELETE posts, postmeta 
        FROM {$wpdb->posts} posts 
        LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id 
        WHERE posts.post_type = 'attachment' 
        AND posts.post_mime_type LIKE 'image/%'
        AND posts.post_parent IN (
            SELECT p.ID FROM (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type IN ('product', 'product_variation')
            ) AS p
        )
    ");
    
    wp_send_json_success(array(
        'urls_deleted' => $urls_deleted,
        'images_deleted' => $images_deleted
    ));
    die();
}


// Helper function to validate Amazon image URL
function is_valid_amazon_image($image_url) {
    // Quick return if empty
    if (empty($image_url)) {
        return false;
    }

    // Check for the Amazon grey-pixel placeholder
    if (strpos($image_url, 'grey-pixel.gif') !== false) {
        return false;  // Invalid image (placeholder)
    }

    // Check for other invalid patterns
    $invalid_patterns = [
        'transparent-pixel',
        'x-locale/common/grey-pixel'
    ];
    
    foreach ($invalid_patterns as $pattern) {
        if (strpos($image_url, $pattern) !== false) {
            return false;  // Invalid image
        }
    }
    
    // If the image is a valid URL with an acceptable extension (jpg, jpeg, png, gif)
    if (preg_match('/\.(jpg|jpeg|png|gif)([?#].*)?$/i', $image_url)) {
        return true;
    }
    
    return false;  // Invalid image URL
}


/**
 * Scrape Amazon reviews from HTML content
 * 
 * @param object $html Simple HTML DOM Parser object
 * @param int $limit Number of reviews to scrape
 * @return array Array of processed reviews
 */
function scrape_amazon_reviews($html, $limit = 10) {
    $reviews = [];
    
    // Find all review elements
    $review_titles = $html->find('.review-title-content');
    $review_texts = $html->find('.review-text-content');
    $review_dates = $html->find('.review-date');
    $review_ratings = $html->find('.review-rating');
    $reviewer_names = $html->find('.a-profile-name');
    $reviewer_images = $html->find('.a-profile-avatar img');

    // Process reviews up to the limit
    foreach ($review_titles as $index => $title) {
        if (count($reviews) >= $limit) {
            break;
        }

        // Get rating
        $rating = $review_ratings[$index]->plaintext ?? 'No rating';
        preg_match('/\d+(\.\d+)?/', $rating, $rating_matches);
        $star_rating = isset($rating_matches[0]) ? floatval($rating_matches[0]) : 0;

        // Get review text
        $text = isset($review_texts[$index]) ? trim(strip_tags($review_texts[$index]->plaintext ?? '')) : '';
        
        // Get title
        $cleaned_title = trim(preg_replace('/^\d+(\.\d+)?\s*out\s*of\s*5\s*stars\s*/', '', $title->plaintext ?? 'No title'));
        
        // Get date
        $date_text = trim($review_dates[$index]->plaintext ?? '');
        $formatted_date = current_time('mysql');
        if (!empty($date_text)) {
            preg_match('/Reviewed\s+in\s+[^\s]+\s+on\s+([^\"]+)/i', $date_text, $date_matches);
            if (!empty($date_matches[1])) {
                $parsed_date = strtotime($date_matches[1]);
                if ($parsed_date) {
                    $formatted_date = date('Y-m-d H:i:s', $parsed_date);
                }
            }
        }

        // Get reviewer name
        $reviewer_name = isset($reviewer_names[$index]) ? trim($reviewer_names[$index]->plaintext) : 'Amazon Customer';
        
        // Get reviewer image
        $reviewer_image = '';
        if (isset($reviewer_images[$index])) {
            $potential_image = $reviewer_images[$index]->src;
            $potential_image = str_replace('._CR0,0,0,0_', '', $potential_image);
            $potential_image = preg_replace('/\._.*?_\./', '.', $potential_image);
            
            if (is_valid_amazon_image($potential_image)) {
                $reviewer_image = $potential_image;
            }
        }

        // Create unique hash for the review
        $review_hash = md5($cleaned_title . $text . $reviewer_name);
        
        // Add review to array
        $reviews[$review_hash] = [
            'title' => $cleaned_title,
            'rating' => $star_rating,
            'date' => $formatted_date,
            'text' => $text,
            'reviewer_name' => $reviewer_name,
            'reviewer_image' => $reviewer_image
        ];
    }

    return $reviews;
}


/**
 * Get unlinked variants/products
 * 
 * Queries the database to find SKUs in the `wp_postmeta` table
 * that are not linked to a valid product or variation in the `wp_posts` table.
 * 
 * @return array List of unlinked variants (SKUs and post IDs).
 */
function get_unlinked_variants() {
    global $wpdb;

    $results = $wpdb->get_results("
        SELECT pm.meta_value AS sku, pm.post_id
        FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_sku'
        AND (p.ID IS NULL OR p.post_status NOT IN ('publish', 'draft', 'pending'))
    ");

    return $results;
}


/**
 * Handle Unlinked Variants Cleanup via AJAX
 */
function handle_unlinked_variants_cleanup() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized access']);
    }

    // Perform cleanup query
    $deleted_rows = $wpdb->query("
        DELETE pm
        FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_sku'
        AND (p.ID IS NULL OR p.post_status NOT IN ('publish', 'draft', 'pending'))
    ");

    if ($deleted_rows === false) {
        wp_send_json_error(['message' => 'Cleanup query failed: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['deleted_count' => $deleted_rows]);
}
add_action('wp_ajax_delete_unlinked_variants_cleanup', 'handle_unlinked_variants_cleanup');



// Shortcode feature: Auto create pages/posts
add_action('wp_ajax_auto_create_pages', 'ams_wc_auto_create_pages');
function ams_wc_auto_create_pages() {
    check_ajax_referer('auto_create_pages_nonce', '_ajax_nonce');

    $type = sanitize_text_field($_POST['type']); // "page" or "post"

    // Prevent duplicates by checking for existing pages/posts
    $existing_single = get_posts([
        'post_type'   => $type,
        'title'       => __('Single Product Display', 'ams-wc-amazon'),
        'post_status' => 'any',
        'numberposts' => 1,
    ]);

    $existing_grid = get_posts([
        'post_type'   => $type,
        'title'       => __('Grid Product Display', 'ams-wc-amazon'),
        'post_status' => 'any',
        'numberposts' => 1,
    ]);

    if ($existing_single || $existing_grid) {
        wp_send_json_error(['message' => __('Pages/Posts already exist.', 'ams-wc-amazon')]);
        return;
    }

    // Create pages/posts
    $single_shortcode_page_id = wp_insert_post([
        'post_title'   => __('Single Product Display', 'ams-wc-amazon'),
        'post_content' => '[ams_display_products ids="123" layout="single"]',
        'post_status'  => 'publish',
        'post_type'    => $type,
    ]);

    $grid_shortcode_page_id = wp_insert_post([
        'post_title'   => __('Grid Product Display', 'ams-wc-amazon'),
        'post_content' => '[ams_display_products ids="123,456,789" columns="3" layout="grid"]',
        'post_status'  => 'publish',
        'post_type'    => $type,
    ]);

    if ($single_shortcode_page_id && $grid_shortcode_page_id) {
        wp_send_json_success();
    } else {
        wp_send_json_error(['message' => __('Error creating Pages/Posts.', 'ams-wc-amazon')]);
    }
}

// Shortcode feature: Delete pages/posts
add_action('wp_ajax_delete_existing_pages', 'ams_wc_delete_existing_pages');
function ams_wc_delete_existing_pages() {
    check_ajax_referer('delete_pages_nonce', '_ajax_nonce');

    $titles = [__('Single Product Display', 'ams-wc-amazon'), __('Grid Product Display', 'ams-wc-amazon')];
    $deleted = false;

    foreach ($titles as $title) {
        $existing = get_posts([
            'post_type'   => ['page', 'post'],
            'title'       => $title,
            'post_status' => 'any',
            'numberposts' => 1,
        ]);

        if (!empty($existing)) {
            wp_delete_post($existing[0]->ID, true); // Force delete
            $deleted = true;
        }
    }

    if ($deleted) {
        wp_send_json_success();
    } else {
        wp_send_json_error(['message' => __('No pages/posts to delete.', 'ams-wc-amazon')]);
    }
}

// ams_deactivation_popup_script
function ams_deactivation_popup_script() {
    $plugin_file = plugin_basename(AMS_PLUGIN_FILE);
    ?>
    <style>
        .ams-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .ams-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-width: 500px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .ams-modal-content h3 {
            margin-top: 0;
            color: #23282d;
        }
        .ams-modal-content p {
            line-height: 1.6;
            margin-bottom: 15px;
            color: #444;
        }
        .ams-modal-content ul {
            margin-left: 20px;
            margin-bottom: 15px;
            color: #444;
        }
        .ams-modal-buttons {
            text-align: right;
            margin-top: 20px;
        }
        .ams-modal-buttons button {
            margin-left: 10px;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .ams-modal-cancel {
            background-color: #f7f7f7;
            border: 1px solid #ccc;
            color: #555;
        }
        .ams-modal-confirm {
            background-color: #0085ba;
            color: white;
            border: none;
        }
        .ams-modal-confirm:hover {
            background-color: #006799;
        }
        .ams-important {
            font-weight: bold;
            color: #d63638;
        }
        .ams-highlight {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .ams-modal-confirm:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .ams-loading::after {
            content: "...";
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { content: "."; }
            33% { content: ".."; }
            66% { content: "..."; }
        }
    </style>
    <div id="ams-deactivation-modal" class="ams-modal">
        <div class="ams-modal-content">
            <h3><?php esc_html_e('Deactivate AMS Plugin', 'ams-wc-amazon'); ?></h3>
            <p><span class="ams-important"><?php esc_html_e('Important:', 'ams-wc-amazon'); ?></span> <?php esc_html_e("We're about to deactivate your license. Here's what you need to know:", 'ams-wc-amazon'); ?></p>
            <ul>
                <li><span class="ams-highlight"><?php esc_html_e('Your license will be automatically deactivated for this site.', 'ams-wc-amazon'); ?></span></li>
                <li><?php esc_html_e('This allows you to', 'ams-wc-amazon'); ?> <span class="ams-important"><?php esc_html_e('reuse the license on another domain', 'ams-wc-amazon'); ?></span> <?php esc_html_e('if needed.', 'ams-wc-amazon'); ?></li>
                <li><?php esc_html_e('Perfect for moving your site or setting up a new installation.', 'ams-wc-amazon'); ?></li>
            </ul>
            <p><span class="ams-important"><?php esc_html_e("When you reactivate the plugin, you'll need to enter your license key again.", 'ams-wc-amazon'); ?></span> <?php esc_html_e("Don't worry, it's a simple process to ensure your license is always where you need it.", 'ams-wc-amazon'); ?></p>
            <p><span class="ams-highlight"><?php esc_html_e('This action helps manage your license across multiple sites.', 'ams-wc-amazon'); ?></span></p>
            <div class="ams-modal-buttons">
                <button class="ams-modal-cancel"><?php esc_html_e('Cancel', 'ams-wc-amazon'); ?></button>
                <button class="ams-modal-confirm"><?php esc_html_e('Confirm Deactivation', 'ams-wc-amazon'); ?></button>
            </div>
        </div>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var $modal = $('#ams-deactivation-modal');
        var $confirmButton = $('.ams-modal-confirm');
        var deactivateUrl = '';

        $('tr[data-plugin="<?php echo esc_js($plugin_file); ?>"] .deactivate a').on('click', function(e) {
            e.preventDefault();
            deactivateUrl = $(this).attr('href');
            $modal.show();
        });

        $('.ams-modal-cancel').on('click', function() {
            $modal.hide();
        });

        $confirmButton.on('click', function() {
            var $button = $(this);
            $button.prop('disabled', true).addClass('ams-loading').text('<?php esc_js(_e('Deactivating', 'ams-wc-amazon')); ?>');

            $.ajax({
                type: 'POST',
                url: amsbackend.ajax_url,
                data: {
                    'nonce': amsbackend.nonce_ams_de_activated,
                    'action': 'ams_license_deactivated',
                },
                success: function(response) {
                    window.location.href = deactivateUrl;
                },
                error: function() {
                    alert('<?php esc_js(_e('Failed to deactivate license. Plugin will still be deactivated.', 'ams-wc-amazon')); ?>');
                    window.location.href = deactivateUrl;
                }
            });
        });

        $(window).on('click', function(e) {
            if ($(e.target).is($modal)) {
                $modal.hide();
            }
        });
    });
    </script>
    <?php
}

// Page speed test
function show_page_load_time() {
    // Check if the feature is enabled
    if (get_option('enable_page_speed_test') !== '1') {
        return;
    }

    // Only show to administrators
    if (!current_user_can('administrator')) {
        return;
    }

    // Get the selected style
    $style = get_option('page_speed_test_style', 'style1');

    // Define styles
    $styles = [
        'style1' => 'background: #333; color: white; font-size: 14px;',
        'style2' => 'background: #f9f9f9; color: black; font-size: 14px;',
        'style3' => 'background: #444; color: white; font-size: 12px; padding: 5px;',
        'style4' => 'background: #222; color: white; font-size: 16px; padding: 15px;',
        'style5' => 'background: #e0e0e0; color: #000; font-size: 14px; font-family: Arial;',
    ];

    // Fallback if style is not defined
    $style_css = $styles[$style] ?? $styles['style1'];

    // Start timing at the beginning
    global $timestart;
    $timestart = microtime(true);

    // Add to footer
    add_action('wp_footer', function() use ($timestart, $style_css) {
        $time = number_format((microtime(true) - $timestart) * 1000, 2);
        $color = $time < 1000 ? '#4CAF50' : ($time < 2000 ? '#FFC107' : '#F44336');

        echo '
        <div style="position: fixed; bottom: 10px; left: 10px; ' . $style_css . ' padding: 10px; border-radius: 5px; z-index: 9999;">
            <strong>Speed Test</strong><br>
            Load Time: <span style="color: ' . $color . '">' . $time . ' ms</span><br>
            Memory: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB
        </div>';
    });
}

// Register shortcode for displaying products in a grid or single product layout
function ams_display_products_grid_shortcode($atts) {
    try {
        // Initialize WooCommerce if not already done
        if (!function_exists('wc_get_product')) {
            return '<p>WooCommerce is not active.</p>';
        }

        $atts = shortcode_atts(
            [
                'ids' => '',       // Comma-separated list of product IDs
                'columns' => 4,    // Number of products per row for multiple products
                'layout' => 'grid' // Layout type: 'grid' (default) or 'single'
            ],
            $atts,
            'ams_display_products'
        );

        // Validate and sanitize inputs
        $product_ids = array_filter(array_map('intval', explode(',', $atts['ids'])));
        $columns = max(1, intval($atts['columns']));

        if (empty($product_ids)) {
            return '<p>No valid product IDs provided.</p>';
        }

        // Get custom button settings
        $use_custom_button = get_option('ams_use_custom_button', '0') === '1';
        $theme_hook = get_option('ams_theme_hook', 'woocommerce_after_shop_loop_item');

        // Create instance of WooCommerceCart class
        $woocommerce_cart = new \Amazon\Affiliate\Frontend\WooCommerceCart();

        ob_start();

        // Legal Notice Logic
        $enable_legal_notice = get_option('enable_legal_notice', '1');
        $legal_notice_text = get_option('legal_notice_text', 'Affiliate-Produkte | Anzeige | Werbung');

        if ($enable_legal_notice === '1') {
            echo '<p class="legal-notice text-muted mb-3" style="font-size: 12px; text-align: left;">'
                . esc_html($legal_notice_text) . '</p>';
        }

        $latest_update = null;
        $alignment = get_option('message_alignment', 'left');
        $alignment_class = $alignment === 'right' ? 'text-end' : 'text-start';

        $enable_product_last_updated = get_option('enable_product_last_updated', '1');
        $product_last_updated_message = get_option('product_last_updated_message', 'Last updated: {date}');

        $enable_global_last_updated = get_option('enable_global_last_updated', '1');
        $global_last_updated_message = get_option('global_last_updated_message', 'Last updated on {date}.');

        if ($atts['layout'] === 'single') {
            // Single product layout
            $product_id = $product_ids[0];
            $product = wc_get_product($product_id);

            if (!$product) {
                ob_end_clean();
                return sprintf('<p>Product ID %d not found.</p>', $product_id);
            }

            $last_updated = get_post_modified_time('F j, Y, g:i a', false, $product_id);
            $latest_update = $last_updated;

            echo '<div class="ams-single-product">';
            echo '<div class="product">';
            
            // Product image with error handling
            $image_html = $product->get_image();
            if (!empty($image_html)) {
                echo '<a href="' . esc_url($product->get_permalink()) . '" class="product-image">' . $image_html . '</a>';
            }
            
            echo '<a href="' . esc_url($product->get_permalink()) . '"><h2 class="product-title">' . esc_html($product->get_name()) . '</h2></a>';
            
            // Rating display with validation
            $rating = $product->get_average_rating();
            if ($rating > 0) {
                echo '<div class="rating-stars" title="' . esc_attr($product->get_rating_count() . ' reviews') . '">'
                    . str_repeat('★', min(5, floor($rating))) 
                    . str_repeat('☆', max(0, 5 - floor($rating))) 
                    . ' <span class="rating-number">(' . esc_html(number_format($rating, 1)) . ')</span></div>';
            }

            // Price display
            $price_html = $product->get_price_html();
            if (!empty($price_html)) {
                echo '<span class="price">' . $price_html . '</span>';
            }

            // Custom button logic
            if ($product->is_purchasable() && $product->is_in_stock()) {
                if ($use_custom_button) {
                    $default_button = sprintf(
                        '<a href="%s" class="button add_to_cart_button">%s</a>',
                        esc_url($product->add_to_cart_url()),
                        esc_html__('Add to Cart', 'woocommerce')
                    );
                    echo $woocommerce_cart->buy_now_button_actions_category($default_button, $product);
                } else {
                    $button_args = [
                        'quantity' => 1,
                        'class' => 'button add_to_cart_button',
                        'attributes' => []
                    ];
                    
                    echo apply_filters(
                        'woocommerce_loop_add_to_cart_link',
                        sprintf(
                            '<a href="%s" data-quantity="%s" class="button add_to_cart_button %s" %s>%s</a>',
                            esc_url($product->add_to_cart_url()),
                            esc_attr($button_args['quantity']),
                            esc_attr($product->is_purchasable() ? 'ajax_add_to_cart' : ''),
                            isset($button_args['attributes']) ? wc_implode_html_attributes($button_args['attributes']) : '',
                            esc_html($product->add_to_cart_text())
                        ),
                        $product,
                        $button_args
                    );
                }
            }

            if ($enable_product_last_updated === '1' && $last_updated) {
                echo '<p class="last-updated ' . esc_attr($alignment_class) . '">'
                    . esc_html(str_replace('{date}', $last_updated, $product_last_updated_message)) . '</p>';
            }

            echo '</div>';
            echo '</div>';
        } else {
            // Grid layout
            echo '<div class="ams-custom-grid">';
            echo '<ul class="ams-grid columns-' . esc_attr($columns) . '" style="--ams-columns: ' . esc_attr($columns) . ';">';

            foreach ($product_ids as $product_id) {
                $product = wc_get_product($product_id);

                if (!$product) {
                    continue;
                }

                $last_updated = get_post_modified_time('F j, Y, g:i a', false, $product_id);
                if (!$latest_update || strtotime($last_updated) > strtotime($latest_update)) {
                    $latest_update = $last_updated;
                }

                echo '<li class="product">';
                
                // Product image with error handling
                $image_html = $product->get_image();
                if (!empty($image_html)) {
                    echo '<a href="' . esc_url($product->get_permalink()) . '" class="product-image">' . $image_html . '</a>';
                }
                
                echo '<a href="' . esc_url($product->get_permalink()) . '"><h2 class="product-title">' . esc_html($product->get_name()) . '</h2></a>';
                
                // Rating display with validation
                $rating = $product->get_average_rating();
                if ($rating > 0) {
                    echo '<div class="rating-stars" title="' . esc_attr($product->get_rating_count() . ' reviews') . '">'
                        . str_repeat('★', min(5, floor($rating))) 
                        . str_repeat('☆', max(0, 5 - floor($rating))) 
                        . ' <span class="rating-number">(' . esc_html(number_format($rating, 1)) . ')</span></div>';
                }

                // Price display
                $price_html = $product->get_price_html();
                if (!empty($price_html)) {
                    echo '<span class="price">' . $price_html . '</span>';
                }

                // Custom button logic
                if ($product->is_purchasable() && $product->is_in_stock()) {
                    if ($use_custom_button) {
                        $default_button = sprintf(
                            '<a href="%s" class="button add_to_cart_button">%s</a>',
                            esc_url($product->add_to_cart_url()),
                            esc_html__('Add to Cart', 'woocommerce')
                        );
                        echo $woocommerce_cart->buy_now_button_actions_category($default_button, $product);
                    } else {
                        $button_args = [
                            'quantity' => 1,
                            'class' => 'button add_to_cart_button',
                            'attributes' => []
                        ];
                        
                        echo apply_filters(
                            'woocommerce_loop_add_to_cart_link',
                            sprintf(
                                '<a href="%s" data-quantity="%s" class="button add_to_cart_button %s" %s>%s</a>',
                                esc_url($product->add_to_cart_url()),
                                esc_attr($button_args['quantity']),
                                esc_attr($product->is_purchasable() ? 'ajax_add_to_cart' : ''),
                                isset($button_args['attributes']) ? wc_implode_html_attributes($button_args['attributes']) : '',
                                esc_html($product->add_to_cart_text())
                            ),
                            $product,
                            $button_args
                        );
                    }
                }

                if ($enable_product_last_updated === '1' && $last_updated) {
                    echo '<p class="last-updated ' . esc_attr($alignment_class) . '">'
                        . esc_html(str_replace('{date}', $last_updated, $product_last_updated_message)) . '</p>';
                }

                echo '</li>';
            }

            echo '</ul>';
            echo '</div>';
        }

        if ($enable_global_last_updated === '1' && $latest_update) {
            echo '<div class="last-updated-overall ' . esc_attr($alignment_class) . '">'
                . esc_html(str_replace('{date}', $latest_update, $global_last_updated_message)) . '</div>';
        }

        $output = ob_get_clean();
        return $output;

    } catch (Exception $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        return '<p>Error displaying products: ' . esc_html($e->getMessage()) . '</p>';
    }
}

// Append custom notification at the end of the content
function append_custom_notification_to_content($content) {
    try {
        // Check if the option is enabled
        if (get_option('enable_custom_notification') === '1') {
            // Determine if we are on a WooCommerce page or a page using the shortcode
            if (is_woocommerce() || has_shortcode($content, 'ams_display_products')) {
                $alignment = get_option('message_alignment', 'left');
                $alignment_class = $alignment === 'right' ? 'text-end' : 'text-start';
                $custom_notification_message = get_option('custom_notification_message', 'Please note: Product details may change.');

                $custom_notification = '<div class="custom-notification ' . esc_attr($alignment_class) . '">';
                $custom_notification .= wp_kses_post($custom_notification_message);
                $custom_notification .= '</div>';

                return $content . $custom_notification;
            }
        }

        return $content;
    } catch (Exception $e) {
        // Log error but return original content to prevent breaking the page
        error_log('Custom notification error: ' . $e->getMessage());
        return $content;
    }
}

// Add last updated date and a static notice to WooCommerce product page
function display_last_updated_date() {
    // Check if the feature is enabled
    if (get_option('enable_last_updated_date') !== '1') {
        return;
    }

    global $product;
    $product_last_updated_message = get_option('product_last_updated_message', 'Last updated: {date}');

    // Check if the custom message is enabled
    if (get_option('enable_custom_message') === '1') {
        // Retrieve the custom message or use the default
        $custom_message = get_option('last_updated_custom_message', 'Important Notice: Product details may change. Please check regularly for updates.');

        // Get the selected style
        $selected_style = get_option('last_updated_notice_style', 'style1');

        // Display the message with the selected style
        echo '<div class="woocommerce-info custom-dynamic-notice ' . esc_attr($selected_style) . '">';
        echo wp_kses_post($custom_message);
        echo '</div>';
    }

    // Get the last modified date
    $last_updated = get_post_modified_time('F j, Y, g:i a', false, $product->get_id());

    if ($last_updated) {
        echo '<p class="product-last-updated">' . esc_html(str_replace('{date}', $last_updated, $product_last_updated_message)) . '</p>';
    }

}

// Filter review title display
function custom_review_title($comment) {
    if (get_option('enable_review_title') == '1') {
        $title = get_comment_meta($comment->comment_ID, 'title', true);
        if (!empty($title)) {
            echo '<strong class="woocommerce-review__title">' . esc_html($title) . '</strong>';
        }
    }
}

// Filter reviewer image
function custom_reviewer_image_url($url, $id_or_email, $args) {
    if (get_option('enable_reviewer_image') != '1') {
        return $url;
    }

    if (is_object($id_or_email) && isset($id_or_email->comment_ID)) {
        $reviewer_image = get_comment_meta($id_or_email->comment_ID, 'reviewer_image', true);
        if (!empty($reviewer_image)) {
            return esc_url($reviewer_image);
        }
    }
    return $url;
}

// Filter reviewer image size
function custom_reviewer_image_data($args, $id_or_email) {
    if (get_option('enable_reviewer_image') != '1') {
        return $args;
    }

    if (is_object($id_or_email) && isset($id_or_email->comment_ID)) {
        $reviewer_image = get_comment_meta($id_or_email->comment_ID, 'reviewer_image', true);
        if (!empty($reviewer_image)) {
            $args['url'] = esc_url($reviewer_image);
            $args['size'] = 60;
        }
    }
    return $args;
}

// Display the GTIN, UPC, EAN, or ISBN code
function display_gtin_in_inventory_tab() {
    global $post;

    // Retrieve the saved GTIN value from post meta
    $gtin = get_post_meta($post->ID, '_gtin', true);

    // Check if GTIN is available and populate the default field
    if ($gtin) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Set the value of the existing field '_global_unique_id'
                $('#_global_unique_id').val('<?php echo esc_js($gtin); ?>');
            });
        </script>
        <?php
    }
}

// Image Fit - shop, category, and product pages
function ams_enqueue_image_fit_css() {
    $image_fit = get_option('ams_image_fit', 'cover'); // Default to 'cover'

    if ($image_fit !== 'none') {
        // Apply the fit styles only if it's not set to 'none'
        $custom_css = "
        /* Product single page images */
        .woocommerce div.product div.images img, 
        .woocommerce div.product div.images img.wp-post-image, 
        .woocommerce-product-gallery__image img {
            object-fit: {$image_fit} !important;
            max-width: 100% !important;
            max-height: 500px !important;
            width: auto !important;
            height: auto !important;
            display: block;
            margin: 0 auto;
        }
        .woocommerce-product-gallery__image {
            max-height: 500px;
            overflow: hidden;
        }

        /* Shop and category page product images */
        .woocommerce ul.products li.product img,
        .woocommerce-page ul.products li.product img,
        .woocommerce ul.products li.product .woocommerce-loop-product__link img,
        .woocommerce-page ul.products li.product .woocommerce-loop-product__link img {
            object-fit: {$image_fit} !important;
            max-width: 100% !important;
            max-height: 300px !important; /* Smaller height for shop/category pages */
            width: auto !important; /* Allow images to keep aspect ratio */
            height: auto !important; /* Auto height for consistent proportions */
            display: block;
            margin: 0 auto;
        }

        /* Container for shop/category page images */
        .woocommerce ul.products li.product .woocommerce-loop-product__link,
        .woocommerce-page ul.products li.product .woocommerce-loop-product__link {
            display: block;
            overflow: hidden; /* Ensure image fits within its container */
        }
        ";
        wp_add_inline_style('wp-block-library', $custom_css);
    } 
}

//Code for variants gallery custom theme
function update_custom_variation_images() {
    global $wpdb;
    // Get the user-defined meta key from plugin settings, default to '_product_image_gallery'
    $custom_meta_key = get_option('variation_image_meta_key', '_product_image_gallery');
    // Check if remote images are enabled
    $use_remote_images = ('Yes' === get_option('ams_remote_amazon_images'));
    // Query to get all variation IDs
    $variation_ids = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts}
        WHERE post_type = 'product_variation' AND post_status = 'publish'
    ");

    foreach ($variation_ids as $variation_id) {
        $gallery_data = array();

        // Get the product gallery images from _product_image_gallery
        $standard_gallery = get_post_meta($variation_id, '_product_image_gallery', true);

        // Ensure standard_gallery is a string before exploding
        if (is_string($standard_gallery) && !empty($standard_gallery)) {
            $gallery_ids = explode(',', $standard_gallery);
            foreach ($gallery_ids as $id) {
                $gallery_data[] = array(
                    'id' => $id,
                    'url' => wp_get_attachment_url($id)
                );
            }
        }

        // Check for woo_variation_gallery_images
        $woo_variation_gallery = maybe_unserialize(get_post_meta($variation_id, 'woo_variation_gallery_images', true));
        if (!empty($woo_variation_gallery) && is_array($woo_variation_gallery)) {
            foreach ($woo_variation_gallery as $id) {
                if (!in_array($id, array_column($gallery_data, 'id'))) {
                    $gallery_data[] = array(
                        'id' => $id,
                        'url' => wp_get_attachment_url($id)
                    );
                }
            }
        }

        // Check for URL-based images
        $url_based_gallery = get_post_meta($variation_id, '_amswoofiu_wcgallary', true);
        $url_based_gallery_array = maybe_unserialize($url_based_gallery);
        if (is_array($url_based_gallery_array)) {
            foreach ($url_based_gallery_array as $image) {
                if (isset($image['url']) && !in_array($image['url'], array_column($gallery_data, 'url'))) {
                    $gallery_data[] = array(
                        'id' => '',
                        'url' => $image['url']
                    );
                }
            }
        }

        // Prepare the final gallery based on the remote images setting
        $final_gallery = array();
        foreach ($gallery_data as $image) {
            if ($use_remote_images) {
                if (!empty($image['url'])) {
                    $final_gallery[] = $image['url'];
                } elseif (!empty($image['id'])) {
                    $final_gallery[] = $image['id'];
                }
            } else {
                if (!empty($image['id'])) {
                    $final_gallery[] = $image['id'];
                } elseif (!empty($image['url'])) {
                    $final_gallery[] = $image['url'];
                }
            }
        }

        // Update the custom meta key with the final gallery
        if (!empty($final_gallery)) {
            update_post_meta($variation_id, $custom_meta_key, $final_gallery);
        } else {
            delete_post_meta($variation_id, $custom_meta_key);
        }
    }
}
// Add a filter to handle both attachment IDs and URLs when displaying images
function handle_url_based_attachment_images($image, $attachment_id, $size, $icon) {
    // If $image is false but we have a URL
    if (!$image && filter_var($attachment_id, FILTER_VALIDATE_URL)) {
        // If it's a URL, return it as an image source
        return array($attachment_id, 800, 800, false); // Assuming a default size of 800x800
    }
    
    // If $image is false and not a URL, return an empty array instead of false
    if ($image === false) {
        return array('', 0, 0, false);
    }
    
    return $image;
}
//Code for variants gallery custom theme



/////START/////
// Tracking Brands
function ams_track_brand_click() {
    if (!isset($_POST['brand_slug']) || !wp_verify_nonce($_POST['nonce'], 'ams_brand_click')) {
        wp_die();
    }
    
    $brand = get_term_by('slug', sanitize_text_field($_POST['brand_slug']), 'product_brand');
    $location = sanitize_text_field($_POST['location']);
    
    // Define valid locations
    $valid_locations = array('product', 'category', 'shop', 'brand_page', 'homepage', 'search', 'archive', 'unknown');
    
    if ($brand) {
        // Get IP address with proper handling for various server configurations
        $ip = '';
        $ip_headers = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    // If there are multiple IPs, take the first one
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                break;
            }
        }
        
        // Get click history for IP throttling
        $ip_clicks = get_option('ams_ip_click_history', array());
        
        // Clean up old IP records (older than 24 hours)
        $current_time = current_time('timestamp');
        foreach ($ip_clicks as $stored_ip => $click_data) {
            if (($current_time - $click_data['last_click']) > (24 * 60 * 60)) {
                unset($ip_clicks[$stored_ip]);
            }
        }
        
        // Check if this IP has clicked this brand recently (within 6 hours)
        $throttle_period = 6 * 60 * 60; // 6 hours in seconds
        if (isset($ip_clicks[$ip]['brands'][$brand->term_id])) {
            $last_click_time = $ip_clicks[$ip]['brands'][$brand->term_id];
            if (($current_time - $last_click_time) < $throttle_period) {
                wp_die(json_encode(array('status' => 'throttled')));
            }
        }
        
        // Update IP click history
        if (!isset($ip_clicks[$ip])) {
            $ip_clicks[$ip] = array(
                'last_click' => $current_time,
                'brands' => array()
            );
        }
        $ip_clicks[$ip]['brands'][$brand->term_id] = $current_time;
        $ip_clicks[$ip]['last_click'] = $current_time;
        
        update_option('ams_ip_click_history', $ip_clicks);
        
        // Handle localhost IP
        if ($ip === '::1' || $ip === 'localhost' || $ip === '127.0.0.1') {
            $country_code = 'LOCAL';
            $country_name = 'Local Development';
        } else {
            // Get country info with proper error handling
            $country_info = wp_remote_get('http://ip-api.com/json/' . $ip, array(
                'timeout' => 5,
                'sslverify' => false
            ));
            
            $country_code = '';
            $country_name = '';
            
            if (!is_wp_error($country_info) && wp_remote_retrieve_response_code($country_info) === 200) {
                $info = json_decode(wp_remote_retrieve_body($country_info));
                if ($info && isset($info->status) && $info->status === 'success') {
                    $country_code = $info->countryCode;
                    $country_name = $info->country;
                }
            }
        }
        
        // Update click statistics
        $clicks = get_option('ams_brand_clicks', array());
        
        // Initialize array structure if it doesn't exist
        if (!isset($clicks[$brand->term_id]) || !is_array($clicks[$brand->term_id])) {
            $clicks[$brand->term_id] = array(
                'total' => 0,
                'product' => 0,
                'category' => 0,
                'shop' => 0,
                'brand_page' => 0,
                'homepage' => 0,
                'search' => 0,
                'archive' => 0,
                'unknown' => 0,
                'clicks_data' => array()
            );
        }
        
        // Store click data
        $clicks[$brand->term_id]['clicks_data'][] = array(
            'time' => current_time('mysql'),
            'ip' => $ip,
            'country_code' => $country_code,
            'country_name' => $country_name,
            'location' => $location
        );
        
        // Keep only last 100 clicks
        if (count($clicks[$brand->term_id]['clicks_data']) > 100) {
            array_shift($clicks[$brand->term_id]['clicks_data']);
        }
        
        // Increment counters
        $clicks[$brand->term_id]['total']++;
        // Only increment location counter if it's a valid location
        if (in_array($location, $valid_locations)) {
            $clicks[$brand->term_id][$location]++;
        }
        
        update_option('ams_brand_clicks', $clicks);
        wp_die(json_encode(array('status' => 'success')));
    }
    
    wp_die();
}
// Tracking Brands

// Add tracking script
function ams_add_brand_tracking() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.ams-brand-container a, .product-brand').on('click', function(e) {
            var $link = $(this);
            var href = $link.attr('href');
            var matches = href.match(/brand\/([^\/]+)/);
            
            if (matches && matches[1]) {
                // Don't prevent default navigation
                var location = 'unknown';
                var body = $('body');
                
                // More specific location checks
                if (body.hasClass('tax-product_cat')) {
                    location = 'category';
                } else if (body.hasClass('post-type-archive-product') || body.hasClass('woocommerce-shop')) {
                    location = 'shop';
                } else if (body.hasClass('single-product')) {
                    location = 'product';
                } else if (body.hasClass('tax-product_brand')) {
                    location = 'brand_page';
                } else if (body.hasClass('home') || body.hasClass('page-template-default')) {
                    location = 'homepage';
                } else if (body.hasClass('search')) {
                    location = 'search';
                } else if (body.hasClass('archive')) {
                    location = 'archive';
                }

                // Use navigator.sendBeacon for tracking without delaying navigation
                var data = new FormData();
                data.append('action', 'ams_track_brand_click');
                data.append('brand_slug', matches[1]);
                data.append('location', location);
                data.append('nonce', '<?php echo wp_create_nonce('ams_brand_click'); ?>');

                // Use sendBeacon for modern browsers (no delay)
                if (navigator.sendBeacon) {
                    navigator.sendBeacon('<?php echo admin_url('admin-ajax.php'); ?>', data);
                } else {
                    // Fallback to async AJAX for older browsers
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'ams_track_brand_click',
                            brand_slug: matches[1],
                            location: location,
                            nonce: '<?php echo wp_create_nonce('ams_brand_click'); ?>'
                        },
                        async: true
                    });
                }
            }
        });
    });
    </script>
    <?php
}

// Register product brand taxonomy
function ams_register_product_brand_taxonomy() {
    if (get_option('ams_brand_module_enabled', '1') !== '1') {
        return;
    }

    $labels = array(
        'name'              => _x('Brands', 'taxonomy general name'),
        'singular_name'     => _x('Brand', 'taxonomy singular name'),
        'search_items'      => __('Search Brands'),
        'all_items'         => __('All Brands'),
        'parent_item'       => __('Parent Brand'),
        'parent_item_colon' => __('Parent Brand:'),
        'edit_item'         => __('Edit Brand'),
        'update_item'       => __('Update Brand'),
        'add_new_item'      => __('Add New Brand'),
        'new_item_name'     => __('New Brand Name'),
        'menu_name'         => __('Brands'),
        'view_item'         => __('View Brand'),
        'back_to_items'     => __('Go to Brands')
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'brand'),
    );

    register_taxonomy('product_brand', array('product'), $args);
}

// Add brand logo field
function ams_add_brand_logo_field($term) {
    $brand_logo = '';
    $image_url = AMS_BRAND_NO_LOGO;
    $is_edit_form = is_object($term);

    if ($is_edit_form) {
        $brand_logo = get_term_meta($term->term_id, 'brand_logo', true);
        $image_url = $brand_logo ? wp_get_attachment_url($brand_logo) : AMS_BRAND_NO_LOGO;
    }

    if ($is_edit_form) {
        // Edit form layout
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="brand_logo"><?php _e('Brand Logo'); ?></label>
            </th>
            <td>
        <?php
    } else {
        // Add form layout
        ?>
        <div class="form-field">
            <label for="brand_logo"><?php _e('Brand Logo'); ?></label>
        <?php
    }
    ?>
            <div class="brand-logo-wrapper">
                <img id="brand-logo-preview" src="<?php echo esc_url($image_url); ?>" 
                     style="max-width: 150px; <?php echo !$brand_logo ? 'opacity: 0.5;' : ''; ?>" />
            </div>
            <input type="hidden" name="brand_logo" id="brand_logo" value="<?php echo esc_attr($brand_logo); ?>">
            <button type="button" class="button" id="upload-brand-logo"><?php _e('Upload Logo'); ?></button>
            <button type="button" class="button" id="remove-brand-logo" 
                    style="<?php echo !$brand_logo ? 'display:none;' : ''; ?>"><?php _e('Remove Logo'); ?></button>
            <p class="description"><?php _e('Upload a logo for the brand or leave empty to use default.'); ?></p>
    <?php
    if ($is_edit_form) {
        echo '</td></tr>';
    } else {
        echo '</div>';
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        var mediaUploader;
        
        $('#upload-brand-logo').click(function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: 'Choose Brand Logo',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#brand_logo').val(attachment.id);
                $('#brand-logo-preview').attr('src', attachment.url).show().css('opacity', '1');
                $('#remove-brand-logo').show();
            });
            
            mediaUploader.open();
        });
        
        $('#remove-brand-logo').click(function() {
            $('#brand_logo').val('');
            $('#brand-logo-preview').attr('src', '<?php echo AMS_BRAND_NO_LOGO; ?>').css('opacity', '0.5');
            $(this).hide();
        });
    });
    </script>
    <?php
}

// Save brand logo
function ams_save_brand_logo($term_id) {
    if (isset($_POST['brand_logo'])) {
        update_term_meta($term_id, 'brand_logo', absint($_POST['brand_logo']));
    }
}

// Register settings with options for logo, product count, and custom CSS
function ams_register_brand_settings() {
    register_setting('ams_brand_settings', 'ams_brand_module_enabled');
    register_setting('ams_brand_settings', 'brand_logo_width');
    register_setting('ams_brand_settings', 'brand_logo_height');
    register_setting('ams_brand_settings', 'brands_per_row');
    register_setting('ams_brand_settings', 'brands_per_page');
    register_setting('ams_brand_settings', 'show_brands_shop_page');
    register_setting('ams_brand_settings', 'show_brands_category_page');
    register_setting('ams_brand_settings', 'display_brand_logo', array('default' => '1'));
    register_setting('ams_brand_settings', 'display_product_count', array('default' => '1'));
    register_setting('ams_brand_settings', 'display_brand_on_product_page', array('default' => '1'));
    register_setting('ams_brand_settings', 'custom_brand_css');
}

// Show brand statistics with last click info
function ams_show_brand_statistics() {
    $brand_clicks = get_option('ams_brand_clicks', array());
    
    echo '<table class="form-table">';
    echo '<thead><tr><th>Brand</th><th>Total Clicks</th><th>Last Click Info</th></tr></thead>';
    echo '<tbody>';

    foreach ($brand_clicks as $brand_id => $data) {
        $brand = get_term($brand_id, 'product_brand');
        if ($brand && !is_wp_error($brand)) {
            $last_click = isset($data['clicks_data']) && !empty($data['clicks_data']) 
                ? end($data['clicks_data']) : null;

            // Format last click info with better display
            $last_click_info = 'No clicks yet';
            if ($last_click) {
                $location_display = $last_click['location'];
                if ($location_display === 'unknown') {
                    $location_display = 'Unknown Page';
                } else {
                    $location_display = ucfirst(str_replace('_', ' ', $location_display));
                }

                // Format display for local development
                $ip_display = $last_click['ip'];
                if ($last_click['country_code'] === 'LOCAL') {
                    $ip_display = '🖥️ Local Dev (' . $ip_display . ')';
                }

                $last_click_info = sprintf(
                    '%s - %s ago - from %s',
                    $ip_display,
                    human_time_diff(strtotime($last_click['time']), current_time('timestamp')),
                    $location_display
                );
            }

            echo '<tr>';
            echo '<td>' . esc_html($brand->name) . '</td>';
            echo '<td>' . intval($data['total']) . '</td>';
            echo '<td>' . $last_click_info . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
}

// Settings page with tabs
function ams_brand_settings_page() {
    ?>
    <div class="wrap">
        <h1>Brand Settings</h1>
        <div id="ams-settings-tabs">
            <button class="tablinks active" onclick="openTab(event, 'moduleActivation')">Module Activation</button>
            <button class="tablinks" onclick="openTab(event, 'displayOptions')">Display Options</button>
            <button class="tablinks" onclick="openTab(event, 'customCSS')">Custom CSS</button>
            <button class="tablinks" onclick="openTab(event, 'statisticsTab')">Statistics</button>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('ams_brand_settings'); ?>

            <!-- Module Activation Tab -->
            <div id="moduleActivation" class="tabcontent" style="display:block;">
                <table class="form-table">
                    <tr>
                        <th>Enable Brands Module</th>
                        <td>
                            <label>
                                <input type="checkbox" name="ams_brand_module_enabled" value="1" 
                                       <?php checked(get_option('ams_brand_module_enabled', '1'), '1'); ?>>
                                Enable brands functionality
                            </label>
                        </td>
                    </tr>
                </table>

                <div class="ams-instructions" style="margin-top: 30px; background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 4px;">
                    <h3>How to Use Brands</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <h4>1. Create a Brands Page</h4>
                        <p>Create a new page in WordPress for displaying all brands. Go to Pages → Add New:</p>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li>Give it a title (e.g., "Our Brands")</li>
                            <li>Add the following shortcode to the page content: <code style="background: #f5f5f5; padding: 3px 6px;">[brand_filter]</code></li>
                            <li>Publish the page</li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <h4>2. Managing Brands</h4>
                        <p>Brands are automatically created when importing products from Amazon. However, you can manage brand details at Products → Brands:</p>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li>Edit brand names if needed</li>
                            <li>Upload or modify brand logos</li>
                            <li>View brand statistics and performance</li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <h4>Available Shortcodes</h4>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li><code style="background: #f5f5f5; padding: 3px 6px;">[brand_filter]</code> - Displays a grid of all brands with logos and product counts</li>
                        </ul>
                    </div>

                    <div class="notice notice-info" style="margin: 20px 0;">
                        <p>💡 <strong>Tip:</strong> Use the Display Options tab to customize how brands are displayed on your site.</p>
                    </div>
                </div>
            </div>

            <!-- Display Options Tab -->
            <div id="displayOptions" class="tabcontent">
                <table class="form-table">
                    <tr>
                        <th>Logo Dimensions</th>
                        <td>
                            <label>Width: <input type="number" name="brand_logo_width" value="<?php echo esc_attr(get_option('brand_logo_width', '150')); ?>"> px</label>
                            <br>
                            <label>Height: <input type="number" name="brand_logo_height" value="<?php echo esc_attr(get_option('brand_logo_height', '150')); ?>"> px</label>
                            <br>
                            <label>
                                <input type="checkbox" name="display_brand_logo" value="1" <?php checked(get_option('display_brand_logo', '1'), '1'); ?>>
                                Display Brand Logo
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>Brands per Row</th>
                        <td>
                            <select name="brands_per_row">
                                <?php
                                $selected = get_option('brands_per_row', '4');
                                for($i = 2; $i <= 6; $i++) {
                                    echo '<option value="' . $i . '" ' . selected($selected, $i, false) . '>' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Brands per Page</th>
                        <td>
                            <input type="number" name="brands_per_page" value="<?php echo esc_attr(get_option('brands_per_page', '12')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>Display Product Count</th>
                        <td>
                            <input type="checkbox" name="display_product_count" value="1" <?php checked(get_option('display_product_count', '1'), '1'); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th>Show Brand on Product Page</th>
                        <td>
                            <input type="checkbox" name="display_brand_on_product_page" value="1" <?php checked(get_option('display_brand_on_product_page', '1'), '1'); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th>Show Brands on Shop Page</th>
                        <td>
                            <input type="checkbox" name="show_brands_shop_page" value="1" <?php checked(get_option('show_brands_shop_page'), '1'); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th>Show Brands on Category Pages</th>
                        <td>
                            <input type="checkbox" name="show_brands_category_page" value="1" <?php checked(get_option('show_brands_category_page'), '1'); ?>>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Custom CSS Tab -->


        <div id="customCSS" class="tabcontent">
                <table class="form-table">
                    <tr>
                        <th>Custom CSS</th>
                        <td>
                            <textarea id="customCSSField" name="custom_brand_css" rows="8" cols="50" class="large-text code"><?php echo esc_textarea(get_option('custom_brand_css')); ?></textarea>
                            
                            <p><strong>Available CSS Classes:</strong></p>
                            <ul style="margin-left: 20px; list-style-type: circle;">
                                <li><code>.ams-brand-container</code> - Wrapper for the entire brand section</li>
                                <li><code>.ams-brand-grid</code> - The grid layout container holding each brand item</li>
                                <li><code>.ams-brand-item</code> - Individual brand item container</li>
                                <li><code>.ams-brand-logo</code> - The brand logo image</li>
                                <li><code>.ams-brand-name</code> - The brand name text</li>
                                <li><code>.ams-brand-count</code> - Product count text below each brand</li>
                                <li><code>.ams-brand-pagination</code> - Container for pagination links</li>
                                <li><code>.product-brand-container</code> - Wrapper for brand on the single product page</li>
                                <li><code>.product-brand</code> - Brand name link on the single product page</li>
                                <li><code>.product-brand-label</code> - Label for brand name on single product page</li>
                            </ul>

                            <p><strong>Example Styles:</strong> Click "Copy" to apply any of the styles below:</p>

                            <div style="margin: 10px 0; padding: 10px; background: #f1f1f1; border: 1px solid #ddd; border-radius: 5px;">
                                <button type="button" onclick="insertCSS('style1')" class="button">Copy Example 1</button>
                                <pre id="style1" style="white-space: pre-wrap;">
            /* Example 1: Modern box shadow effect */
            .ams-brand-container { max-width: 1200px; margin: 0 auto; padding: 10px; }
            .ams-brand-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
            .ams-brand-item { padding: 20px; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; }
            .ams-brand-logo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
            .ams-brand-name { font-weight: bold; color: #333; margin-top: 10px; }
            .ams-brand-count { font-size: 0.9em; color: #666; }
            .ams-brand-pagination { text-align: center; margin-top: 20px; }

            /* Product page brand styling */
            .product-brand-container { background-color: #f9f9f9; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; margin-top: 20px; }
            .product-brand { color: #0073aa; font-weight: bold; font-size: 1.2em; text-decoration: none; }
            .product-brand:hover { color: #005580; text-decoration: underline; }
            .product-brand-label { font-size: 1.1em; color: #333; margin-right: 5px; }
                                </pre>
                            </div>

                            <div style="margin: 10px 0; padding: 10px; background: #f1f1f1; border: 1px solid #ddd; border-radius: 5px;">
                                <button type="button" onclick="insertCSS('style2')" class="button">Copy Example 2</button>
                                <pre id="style2" style="white-space: pre-wrap;">
            /* Example 2: Minimalist border style */
            .ams-brand-container { max-width: 1200px; margin: 0 auto; padding: 10px; }
            .ams-brand-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
            .ams-brand-item { border: 1px solid #ddd; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
            .ams-brand-logo { width: 80px; height: 80px; object-fit: cover; }
            .ams-brand-name { font-size: 1.2em; color: #0073aa; margin-top: 10px; }
            .ams-brand-count { font-style: italic; color: #555; }
            .ams-brand-pagination { text-align: center; margin-top: 20px; }

            /* Product page brand styling */
            .product-brand-container { background-color: #f9f9f9; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; margin-top: 20px; }
            .product-brand { color: #0073aa; font-weight: bold; font-size: 1.2em; text-decoration: none; }
            .product-brand:hover { color: #005580; text-decoration: underline; }
            .product-brand-label { font-size: 1.1em; color: #333; margin-right: 5px; }
                                </pre>
                            </div>

                            <div style="margin: 10px 0; padding: 10px; background: #f1f1f1; border: 1px solid #ddd; border-radius: 5px;">
                                <button type="button" onclick="insertCSS('style3')" class="button">Copy Example 3</button>
                                <pre id="style3" style="white-space: pre-wrap;">
            /* Example 3: Dark theme with hover effect */
            .ams-brand-container { max-width: 1200px; background: #333; padding: 20px; border-radius: 10px; }
            .ams-brand-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
            .ams-brand-item { padding: 15px; background: #444; color: #ddd; border: 1px solid #555; transition: transform 0.3s; }
            .ams-brand-item:hover { transform: scale(1.05); }
            .ams-brand-logo { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; }
            .ams-brand-name { color: #f0f0f0; font-size: 1.2em; margin-top: 10px; }
            .ams-brand-count { color: #aaa; }
            .ams-brand-pagination { text-align: center; color: #f1f1f1; margin-top: 20px; }

            /* Product page brand styling */
            .product-brand-container { background-color: #444; padding: 8px; border: 1px solid #555; border-radius: 4px; text-align: center; margin-top: 20px; }
            .product-brand { color: #eaeaea; font-weight: bold; font-size: 1.2em; text-decoration: none; }
            .product-brand:hover { color: #ddd; text-decoration: underline; }
            .product-brand-label { font-size: 1.1em; color: #ccc; margin-right: 5px; }
                                </pre>
                            </div>
                        </td>
                    </tr>
                </table>
        </div>

        <!-- Statistics Tab -->
        <div id="statisticsTab" class="tabcontent" style="width: 100%; max-width: 1200px; margin: 0 auto;">
            <h2>Brand Click Statistics</h2>
            
            <!-- Search and Reset Button Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <!-- Search controls -->
                <div style="display: flex; align-items: center; width: 70%;">
                    <input type="text" name="brand_search" 
                           value="<?php echo esc_attr(isset($_GET['brand_search']) ? $_GET['brand_search'] : ''); ?>" 
                           placeholder="Search brands..." 
                           style="width: 100%; padding: 8px; border-radius: 4px; margin-right: 10px;">
                    <button type="button" class="button button-primary" onclick="window.location.href='?page=brand-settings&brand_search=' + encodeURIComponent(this.previousElementSibling.value)">Search</button>
                    <?php if (isset($_GET['brand_search'])): ?>
                        <a href="?page=brand-settings" class="button button-secondary" style="margin-left: 10px;">Clear</a>
                    <?php endif; ?>
                </div>

                <!-- Reset Button -->
                <button type="button" class="button button-secondary" 
                        onclick="if(confirm('Are you sure you want to reset all brand statistics? This cannot be undone!')) { 
                                    jQuery.post(ajaxurl, {
                                        action: 'ams_reset_stats',
                                        nonce: '<?php echo wp_create_nonce('reset_brand_stats'); ?>'
                                    }, function() {
                                        window.location.reload();
                                    });
                                }"
                        style="color: #dc3232;">Reset All Statistics
                </button>
            </div>

            <?php
            // Display reset success message
            if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
                echo '<div class="notice notice-success is-dismissible"><p>Statistics reset successfully!</p></div>';
            }
            ?>

            <?php
            $brand_clicks = get_option('ams_brand_clicks', array());

            if (!empty($brand_clicks)) {
                $paged = isset($_GET['stat_page']) ? max(1, intval($_GET['stat_page'])) : 1;
                $search = isset($_GET['brand_search']) ? sanitize_text_field($_GET['brand_search']) : '';
                $per_page = 10;

                arsort($brand_clicks);

                // Filter brands by search
                if ($search) {
                    $filtered_clicks = array();
                    foreach ($brand_clicks as $brand_id => $data) {
                        $brand = get_term($brand_id, 'product_brand');
                        if ($brand && stripos($brand->name, $search) !== false) {
                            $filtered_clicks[$brand_id] = $data;
                        }
                    }
                    $brand_clicks = $filtered_clicks;
                }

                // Pagination calculations
                $total_items = count($brand_clicks);
                $total_pages = ceil($total_items / $per_page);
                $offset = ($paged - 1) * $per_page;

                // Slice array for current page
                $page_clicks = array_slice($brand_clicks, $offset, $per_page, true);

                echo '<table class="widefat fixed striped" style="width: 100%; margin-top: 20px;">';
                echo '<thead>
                        <tr style="background: #f3f3f3;">
                            <th style="width: 15%; text-align: left;">Brand Name</th>
                            <th style="width: 10%; text-align: center;">Total Clicks</th>
                            <th style="width: 10%; text-align: center;">Shop Page</th>
                            <th style="width: 10%; text-align: center;">Category Page</th>
                            <th style="width: 10%; text-align: center;">Product Page</th>
                            <th style="width: 10%; text-align: center;">Brand Page</th>
                            <th style="width: 10%; text-align: center;">Products</th>
                            <th style="width: 25%; text-align: left;">Last Click Info</th>
                        </tr>
                      </thead>';
                echo '<tbody>';

                foreach ($page_clicks as $brand_id => $data) {
                    $brand = get_term($brand_id, 'product_brand');
                    if ($brand && !is_wp_error($brand)) {
                        // Make sure all data keys exist with defaults
                        $total = isset($data['total']) ? $data['total'] : 0;
                        $shop = isset($data['shop']) ? $data['shop'] : 0;
                        $category = isset($data['category']) ? $data['category'] : 0;
                        $product = isset($data['product']) ? $data['product'] : 0;
                        $brand_page = isset($data['brand_page']) ? $data['brand_page'] : 0;

                        // Get last click data
                        $last_click = isset($data['clicks_data']) && !empty($data['clicks_data']) 
                            ? end($data['clicks_data']) : null;

                        // Format last click info
                        $last_click_info = 'No clicks yet';
                        if ($last_click) {
                            $ip_info = '';
                            if ($last_click['country_code'] === 'LOCAL') {
                                $ip_info = '🖥️ ' . $last_click['ip']; // Computer emoji for localhost
                            } else {
                                $flag_img = !empty($last_click['country_code']) 
                                    ? '<img src="https://flagcdn.com/16x12/' . strtolower($last_click['country_code']) . '.png" 
                                         style="margin-right:5px; vertical-align:middle;" 
                                         title="' . esc_attr($last_click['country_name']) . '" />' 
                                    : '';
                                $ip_info = $flag_img . $last_click['ip'];
                            }
                            
                            $last_click_info = sprintf(
                                '%s - %s ago - from %s',
                                $ip_info,
                                human_time_diff(strtotime($last_click['time']), current_time('timestamp')),
                                ucfirst($last_click['location'])
                            );
                        }

                        echo '<tr>';
                        echo '<td><a href="' . esc_url(get_term_link($brand)) . '" target="_blank">' 
                             . esc_html(str_replace('Visita la tienda de ', '', $brand->name)) . '</a></td>';
                        echo '<td style="text-align: center;">' . number_format($total) . '</td>';
                        echo '<td style="text-align: center;">' . number_format($shop) . '</td>';
                        echo '<td style="text-align: center;">' . number_format($category) . '</td>';
                        echo '<td style="text-align: center;">' . number_format($product) . '</td>';
                        echo '<td style="text-align: center;">' . number_format($brand_page) . '</td>';
                        echo '<td style="text-align: center;">' . number_format($brand->count) . '</td>';
                        echo '<td style="text-align: left;">' . $last_click_info . '</td>';
                        echo '</tr>';
                    }
                }
                echo '</tbody></table>';

                // Pagination
                if ($total_pages > 1) {
                    echo '<div class="tablenav"><div class="tablenav-pages" style="margin-top: 15px; text-align: center;">';
                    echo paginate_links(array(
                        'base' => add_query_arg('stat_page', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    echo '</div></div>';
                }

                echo '<p style="margin-top: 15px; font-weight: bold; color: #333;">';
                echo 'Total Clicks Across All Brands: ' . number_format(array_sum(array_column($brand_clicks, 'total')));
                echo '</p>';
            } else {
                echo '<p style="margin-top: 15px; color: #666;">No brand statistics available yet.</p>';
            }
            ?>
        </div>

            <?php submit_button(); ?>

        <style>
            /* Tab button styling */
            #ams-settings-tabs {
                margin-bottom: 10px;
            }
            .tablinks {
                background-color: #f1f1f1;
                border: 1px solid #ccc;
                border-bottom: none;
                color: #0073aa;
                padding: 10px 15px;
                cursor: pointer;
                display: inline-block;
                margin-right: 5px;
            }
            .tablinks.active {
                background-color: #fff;
                border-bottom: 1px solid #fff;
                font-weight: bold;
            }
            /* Tab content area styling */
            .tabcontent {
                display: none;
                padding: 20px;
                border: 1px solid #ccc;
                background-color: #fff;
            }
        </style>

        <script>
            function openTab(event, tabName) {
                var i, tabcontent, tablinks;
                tabcontent = document.getElementsByClassName("tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                document.getElementById(tabName).style.display = "block";
                event.currentTarget.className += " active";
                localStorage.setItem('amsActiveTab', tabName);
            }
            jQuery(document).ready(function($) {
                var savedTab = localStorage.getItem('amsActiveTab');
                if (savedTab) {
                    document.querySelector('button[onclick*="' + savedTab + '"]').click();
                } else {
                    document.getElementsByClassName('tablinks')[0].click();
                }
            });
        </script>

        <script>
            // JavaScript function to insert CSS code into the custom CSS field
            function insertCSS(styleId) {
                var cssContent = document.getElementById(styleId).textContent;
                document.getElementById('customCSSField').value = cssContent;
                alert('CSS added to the custom CSS box. Please save your changes.');
            }
        </script>

        <script>
            function copyCSS(styleId) {
                var copyText = document.getElementById(styleId).textContent;
                navigator.clipboard.writeText(copyText).then(function() {
                    alert('CSS copied to clipboard');
                }, function(err) {
                    console.error('Error copying text: ', err);
                });
            }
        </script>
        </form>
    </div>
    <?php
}

// Reset Statistics
function ams_reset_stats_handler() {
    // Verify nonce for security
    check_ajax_referer('reset_brand_stats', 'nonce');

    // Delete both options
    delete_option('ams_brand_clicks');
    delete_option('ams_ip_click_history');

    // Verify deletion using direct SQL queries
    global $wpdb;

    // Check if any data remains in options table
    $remaining_data = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name IN ('ams_brand_clicks', 'ams_ip_click_history')"
        )
    );

    // If there's still data, try to force delete it
    if (!empty($remaining_data)) {
        foreach ($remaining_data as $data) {
            $wpdb->delete(
                $wpdb->options,
                array('option_name' => $data->option_name),
                array('%s')
            );
        }
    }

    // Verify again after force delete
    $verification = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name IN ('ams_brand_clicks', 'ams_ip_click_history')"
        )
    );

    // Send back detailed response
    wp_send_json_success(array(
        'message' => 'Reset attempted',
        'remaining_data' => empty($verification) ? 'All data cleared' : 'Some data remains',
        'details' => $verification
    ));
}

// Function to inject custom CSS into the header for both brand and product pages
function ams_inject_custom_css() {
    $custom_css = get_option('custom_brand_css');
    if (!empty($custom_css)) {
        echo '<style>' . esc_html($custom_css) . '</style>';
    }
}

// Add admin menu for brand settings
function ams_add_brand_admin_menu() {
    add_options_page(
        'Brand Settings',
        'Brand Settings',
        'manage_options',
        'brand-settings',
        'ams_brand_settings_page'
    );
}

// Display brand name on single product page if enabled
function ams_display_product_brand() {
    if (get_option('display_brand_on_product_page', '1') !== '1') {
        return;
    }

    global $product;
    if ($product) {
        $terms = get_the_terms($product->get_id(), 'product_brand');
        if ($terms && !is_wp_error($terms)) {
            $brand = array_shift($terms);
            echo '<div class="product-brand-container">';  // Container with a specific class
            echo '<span class="product-brand-label">' . esc_html__('Brand: ') . '</span>';
            echo '<a href="' . esc_url(get_term_link($brand)) . '" class="product-brand">' . esc_html($brand->name) . '</a>';
            echo '</div>';
        }
    }
}

// Brand filter shortcode with isolated pagination and improved styling
function ams_brand_filter_shortcode($atts) {
    if (get_option('ams_brand_module_enabled', '1') !== '1') {
        return '<div style="text-align: center; margin-top: 20px; font-size: 16px; color: #555; background-color: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    Brand module is disabled.
                </div>';
    }

    // Inject custom CSS if provided
    $custom_css = get_option('custom_brand_css');
    if (!empty($custom_css)) {
        echo '<style>' . esc_html($custom_css) . '</style>';
    }

    // Add custom CSS for pagination and layout
    echo '<style>
        .ams-brand-container {
            margin: 20px 0;
        }
        .woocommerce-pagination {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            clear: both;
        }
        .woocommerce-pagination ul {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .woocommerce-pagination li {
            margin: 0 5px;
        }
        .woocommerce-pagination a, .woocommerce-pagination span {
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #333;
            border-radius: 3px;
            background-color: #f7f7f7;
            transition: background-color 0.3s;
        }
        .woocommerce-pagination a:hover {
            background-color: #e2e2e2;
        }
        .woocommerce-pagination .current {
            background-color: #0073aa;
            color: #fff;
            border-color: #0073aa;
        }
    </style>';

    // Define the pagination setup for the brand filter only
    $brands_per_page = intval(get_option('brands_per_page', 12));
    $brands_per_row = intval(get_option('brands_per_row', 4));
    $logo_width = get_option('brand_logo_width', '150');
    $logo_height = get_option('brand_logo_height', '150');
    $display_logo = get_option('display_brand_logo', '1');
    $display_count = get_option('display_product_count', '1');
    $paged = isset($_GET['brand_page']) ? max(1, intval($_GET['brand_page'])) : 1;

    $args = array(
        'taxonomy' => 'product_brand',
        'hide_empty' => true,
        'number' => $brands_per_page,
        'offset' => ($paged - 1) * $brands_per_page
    );

    // Fetch brands with the defined arguments
    $terms = get_terms($args);
    $total_terms = wp_count_terms('product_brand', array('hide_empty' => true));
    $total_pages = ceil($total_terms / $brands_per_page);

    // Output container
    $output = '<div class="ams-brand-container">';

    // Check and display terms if available
    if (!empty($terms) && !is_wp_error($terms)) {
        $output .= '<div class="ams-brand-grid" style="display: grid; grid-template-columns: repeat(' . $brands_per_row . ', 1fr); gap: 20px;">';
        
        foreach ($terms as $term) {
            $brand_logo_id = get_term_meta($term->term_id, 'brand_logo', true);
            $brand_logo_url = $brand_logo_id ? wp_get_attachment_url($brand_logo_id) : AMS_BRAND_NO_LOGO;

            $output .= '<div class="ams-brand-item" style="text-align: center;">';
            $output .= '<a href="' . esc_url(get_term_link($term)) . '">';

            // Display logo if enabled
            if ($display_logo) {
                $output .= '<img src="' . esc_url($brand_logo_url) . '" alt="' . esc_attr($term->name) . '" style="width:' . esc_attr($logo_width) . 'px; height:' . esc_attr($logo_height) . 'px; object-fit: contain; margin-bottom: 10px;" />';
            }

            // Center brand name if logo is disabled
            $output .= '<div class="ams-brand-name" style="font-weight:bold; ' . (!$display_logo ? 'margin-top: 20px;' : '') . '">' . esc_html($term->name) . '</div>';

            // Display product count if enabled
            if ($display_count) {
                $output .= '<div class="ams-brand-count">' . esc_html($term->count) . ' Products</div>';
            }

            $output .= '</a>';
            $output .= '</div>';
        }
        
        $output .= '</div>';

        // Custom Pagination
        if ($total_pages > 1) {
            $output .= '<nav class="woocommerce-pagination" aria-label="Brand Pagination">';
            $output .= paginate_links(array(
                'base' => add_query_arg('brand_page', '%#%'),
                'format' => '',
                'current' => $paged,
                'total' => $total_pages,
                'prev_text' => __('<<', 'text-domain'),
                'next_text' => __('>>', 'text-domain'),
                'type' => 'list',
                'mid_size' => 2,
                'end_size' => 1,
                'add_args' => array(),
                'add_fragment' => ''
            ));
            $output .= '</nav>';
        }

    } else {
        $output .= '<p>No brands found.</p>';
    }

    $output .= '</div>';
    return $output;
}

// Conditionally display brand filter on shop and category pages
function ams_display_brand_filter() {
    if (get_option('ams_brand_module_enabled', '1') !== '1') {
        return;
    }

    if ((is_shop() && get_option('show_brands_shop_page')) || 
        (is_product_category() && get_option('show_brands_category_page'))) {
        echo do_shortcode('[brand_filter]');
    }
}
/////END/////

function ams_enqueue_admin_scripts($hook) {
    // Check if the current page is the brand taxonomy edit or create page
    if ('edit-tags.php' === $hook || 'term.php' === $hook) {
        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'product_brand') {
            wp_enqueue_media(); // WordPress media uploader
            wp_enqueue_script('brand-logo-upload', AMS_PLUGIN_URL . 'assets/js/brand-logo-upload.js', array('jquery'), null, true);
        }
    }

    // Enqueue Thickbox on plugins page (existing code)
    if ('plugins.php' === $hook) {
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
    }
}

function ams_plugin_information_content($false, $action, $response) {
   if ('plugin_information' != $action || 'ams-wc-amazon' != $response->slug) {
       return $false;
   }
   global $wp_version;
   
   // Get plugin header data
   $plugin_data = get_file_data(AMS_PLUGIN_FILE, array(
       'Name' => 'Plugin Name',
       'Slug' => 'Slug',
       'Version' => 'Version',
       'Author' => 'Author',
       'AuthorURI' => 'Author URI',
       'PluginURI' => 'Plugin URI',
       'Requires' => 'Requires at least',
       'Tested' => 'Tested up to',
       'LastUpdated' => 'Last Updated',
       'Downloads' => 'Downloads'
   ));

   // Enqueue styles and scripts
   wp_enqueue_style('ams-lightbox-style', AMS_PLUGIN_URL . 'assets/css/custom.css', array(), $plugin_data['Version']);
   wp_enqueue_script('ams-lightbox-script', AMS_PLUGIN_URL . 'assets/js/custom.js', array('jquery'), $plugin_data['Version'], true);

   // Set response data from plugin header
   $response->name = $plugin_data['Name'];
   $response->slug = $plugin_data['Slug'];
   $response->version = $plugin_data['Version'];
   $response->author = sprintf('<a href="%s">%s</a>', esc_url($plugin_data['AuthorURI']), esc_html($plugin_data['Author']));
   $response->homepage = esc_url($plugin_data['PluginURI']);
   $response->requires = $plugin_data['Requires'];
   $response->tested = $wp_version;
   $response->compatibility = array($wp_version => array($wp_version => array(100, 0, 0)));
   $response->downloaded = intval($plugin_data['Downloads']);
   $response->last_updated = $plugin_data['LastUpdated'];

    $response->sections = array(
        'description' => '<strong>AMS - The Ultimate WooCommerce Amazon Affiliate Plugin</strong>

        <p>Transform your WooCommerce store into a powerful Amazon affiliate platform. Easily search and import Amazon products with or without API keys, create a dropshipping store, and maximize your earnings.</p>

        <h3>Key Features:</h3>
        <ul>
            <li><strong>Effortless Integration:</strong> Import Amazon products directly into your WooCommerce store</li>
            <li><strong>Maximize Earnings:</strong> Boost sales with dynamic Amazon buy buttons and extended commission periods</li>
            <li><strong>Streamlined Operations:</strong> Automate product import, updates, and checkout processes</li>
            <li><strong>Global Marketplace Support:</strong> Compatible with multiple Amazon countries</li>
            <li><strong>Comprehensive Product Management:</strong> Auto-sync titles, prices, reviews, weights, and more</li>
            <li><strong>Robust API Support:</strong> Leverage Amazon\'s PA API 5.0 for advanced capabilities (optional)</li>
            <li><strong>Flexible Checkout Options:</strong> Choose between WooCommerce or Amazon checkout</li>
            <li><strong>Customization:</strong> Editable buy buttons, product details, and automatic currency conversion</li>
        </ul>

        <p>Whether you\'re a seasoned affiliate marketer or just starting, AMS provides all the tools you need to create and manage a successful Amazon affiliate store within your WooCommerce environment.</p>',

        'installation' => '<strong>INSTALLATION INSTRUCTIONS</strong>
            <ol>
                <li>Download the  plugin ZIP file from your CodeCanyon account.</li>
                <li>Log in to your WordPress admin panel.</li>
                <li>Navigate to Plugins > Add New.</li>
                <li>Click on the "Upload Plugin" button at the top of the page.</li>
                <li>Choose the downloaded ZIP file and click "Install Now".</li>
                <li>After installation, click "Activate Plugin".</li>
            </ol>

            <h3>Post-Installation Setup</h3>
            <ol>
                <li>After activating the plugin, you need to activate your license.</li>
                <li>To find your purchase code (license key):
                    <ul>
                        <li>Log in to your Envato account.</li>
                        <li>Go to your Downloads page.</li>
                        <li>Click "Download" next to .</li>
                        <li>Choose "License certificate & purchase code (text)" from the dropdown.</li>
                    </ul>
                    For more detailed instructions, refer to <a href="kb-article/how-to-find-your-ams-woocommerce-amazon-purchase-code/" target="_blank">Envato\'s guide on finding your purchase code</a>.
                </li>
                <li>Once you have your purchase code:
                    <ul>
                        <li>Go to the AMS plugin dashboard in your WordPress admin panel.</li>
                        <li>Locate the license activation box.</li>
                        <li>Paste your purchase code into the provided field.</li>
                        <li>Click "Activate".</li>
                    </ul>
                </li>
                <li>After successful activation, it\'s recommended to review and adjust the plugin settings to suit your needs.</li>
            </ol>

            <p>For more detailed setup instructions and troubleshooting, please refer to our <a href="knowledge-base/" target="_blank">Knowledge Base</a>.</p>
        ',
        'faq' => '
        <h3>Frequently Asked Questions</h3>

        <p><strong>Q: Do I need an Amazon API key to use this plugin?</strong></p>
        <p>A: No, you can use AMS without an API key. However, using an API key (PA API 5.0) unlocks additional features and capabilities.</p>

        <p><strong>Q: Can I import products from multiple Amazon marketplaces?</strong></p>
        <p>A: Yes, AMS supports global marketplace integration, allowing you to import products from various Amazon marketplaces worldwide.</p>

        <p><strong>Q: How does the plugin handle product updates?</strong></p>
        <p>A: AMS automatically syncs and updates product information, including titles, prices, reviews, and more, ensuring your store always has the latest data from Amazon.</p>

        <p><strong>Q: Can I customize the "Buy on Amazon" buttons?</strong></p>
        <p>A: Absolutely! The plugin offers customizable, dynamic buy buttons to match your store\'s design and maximize conversions.</p>

        <p><strong>Q: Does AMS support dropshipping?</strong></p>
        <p>A: Yes, AMS includes a built-in module for dropshipping operations, making it easy to set up and manage a dropshipping business.</p>

        <p><strong>Q: How does the checkout process work?</strong></p>
        <p>A: You have flexibility in the checkout process. You can choose to have customers add products to the cart in your WooCommerce store and then redirect them to Amazon for the final purchase, or complete the checkout on your site.</p>

        <p><strong>Q: Can I import products without using URLs?</strong></p>
        <p>A: Yes, AMS offers versatile import options. You can import products using URLs, category searches, or keyword searches.</p>

        <p><strong>Q: Does the plugin support affiliate commissions?</strong></p>
        <p>A: Yes, AMS fully supports affiliate commissions, including options for 24-hour and 90-day cookie durations to maximize your earning potential.</p>

        <p><strong>Q: Is automatic currency conversion supported?</strong></p>
        <p>A: Yes, AMS includes automatic currency conversion to ensure pricing is displayed correctly for your local market.</p>

        <p><strong>Q: Can I manage multiple stores with this plugin?</strong></p>
        <p>A: While you can import from multiple Amazon marketplaces, all imported products are managed within a single WooCommerce store for streamlined operations.</p>

        <p><strong>Q: Is there a demo available to test the plugin?</strong></p>
        <p>A: Yes, we offer a demo site where you can experience AMS in action. You can access it using the provided demo credentials.</p>
        ',
        'changelog' => sprintf('
            <h3>Updates Log – Version %s</h3>
            <p>We regularly update our plugin to bring you new features, improvements, and bug fixes. To see the detailed changelog for this version, please visit our official changelog page:</p>
            <p><a href="woocommerce-amazon-logs/">View Full Changelog</a></p>
            <p>Thank you for using our plugin. We\'re committed to continually enhancing your experience!</p>
            ',
            get_file_data(AMS_PLUGIN_FILE, array('Version' => 'Version'))['Version']
        ),
        'screenshots' => '
            <h3>Screenshots</h3>
            <ol>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/1.png') . '" alt="AMS Dashboard" class="lightbox-image">
                    <p>AMS Dashboard</p>
                </li>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/2.png') . '" alt="AMS - Import Amazon products with API by Search and CSV" class="lightbox-image">
                    <p>AMS - Import Amazon products with API by Search and CSV</p>
                </li>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/3.png') . '" alt="AMS - Import Amazon products without-API by URL" class="lightbox-image">
                    <p>AMS - Import Amazon products without-API by URL</p>
                </li>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/4.png') . '" alt="AMS - Import Amazon products without-API by search" class="lightbox-image">
                    <p>AMS - Import Amazon products without-API by search</p>
                </li>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/5.png') . '" alt="AMS - Import Amazon products reviews without-API" class="lightbox-image">
                    <p>AMS - Import Amazon products reviews without-API</p>
                </li>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/6.png') . '" alt="AMS - Configuration[proxy services, settings, etc.]" class="lightbox-image">
                    <p>AMS - Configuration[proxy services, settings, etc.]</p>
                </li>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/7.png') . '" alt="AMS - Amazon API Settings key and test status" class="lightbox-image">
                    <p>AMS - Amazon API Settings key and test status</p>
                </li>
                <li>
                    <img src="' . esc_url(AMS_PLUGIN_URL . 'assets/screenshots/8.png') . '" alt="AMS - Update Amazon products - All Types" class="lightbox-image">
                    <p>AMS - Update Amazon products - All Types</p>
                </li>
            </ol>
        ',
    );
    $response->download_link = '';

    return $response;
}

/////START/////
//Backend code for product meta box - admin side
function ams_modify_product_image_meta_boxes() {
    remove_meta_box('postimagediv', 'product', 'side');
    add_meta_box('postimagediv', __('Product image'), 'ams_product_image_meta_box_content', 'product', 'side', 'low');
}
function ams_product_image_meta_box_content($post) {
    $post_id = $post->ID;
    
    // Display the original product image meta box content
    echo _wp_post_thumbnail_html(get_post_thumbnail_id($post_id), $post_id);

    // Display URL-based featured image if available
    $image_url = get_post_meta($post_id, '_thumbnail_id_url', true);
    if (!$image_url) {
        $ams_image_data = get_post_meta($post_id, '_amswoofiu_url', true);
        $ams_image_array = maybe_unserialize($ams_image_data);
        $image_url = is_array($ams_image_array) && isset($ams_image_array['img_url']) ? $ams_image_array['img_url'] : '';
    }
    
    if ($image_url) {
        echo '<div class="ams-url-featured-image">';
        echo '<p>' . __('URL-based Featured Image:') . '</p>';
        echo '<img src="' . esc_url($image_url) . '" style="max-width:100%;height:auto;">';
        echo '</div>';
    }
}
// Add URL-based images to the product gallery
function ams_add_url_images_to_product_gallery() {
    global $post;
    $product_id = $post->ID;
    $gallery_images = get_post_meta($product_id, '_amswoofiu_wcgallary', true);
    $gallery_images_array = maybe_unserialize($gallery_images);
    
    if (is_array($gallery_images_array) && !empty($gallery_images_array)) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var galleryContainer = $('#product_images_container ul.product_images');
                <?php
                foreach ($gallery_images_array as $index => $image) {
                    if (isset($image['url'])) {
                        ?>
                        galleryContainer.append('<li class="image" data-attachment_id="url_<?php echo esc_js($index); ?>">' +
                            '<img src="<?php echo esc_js($image['url']); ?>" alt="" />' +
                            '<ul class="actions">' +
                            '<li><a href="#" class="delete" title="Delete image">Delete</a></li>' +
                            '</ul>' +
                            '</li>');
                        <?php
                    }
                }
                ?>
                // Reinitialize sortable
                if (typeof $.fn.sortable !== 'undefined') {
                    galleryContainer.sortable('refresh');
                }
            });
        </script>
        <?php
    }
}
// Handle deletion of URL-based images
function ams_handle_url_image_deletion() {
    if (isset($_POST['id']) && strpos($_POST['id'], 'url_') === 0) {
        $product_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $index = substr($_POST['id'], 4);
        $gallery_images = get_post_meta($product_id, '_amswoofiu_wcgallary', true);
        $gallery_images_array = maybe_unserialize($gallery_images);
        
        if (isset($gallery_images_array[$index])) {
            unset($gallery_images_array[$index]);
            update_post_meta($product_id, '_amswoofiu_wcgallary', $gallery_images_array);
        }
        
        wp_send_json_success();
        exit;
    }
}
// Add custom CSS to ensure proper display
function ams_add_product_image_styles() {
    ?>
    <style>
        .ams-url-featured-image {
            margin-top: 1em;
            border-top: 1px solid #ddd;
            padding-top: 1em;
        }
        #product_images_container .image {
            width: 80px;
            height: 80px;
            float: left;
            margin: 3px;
            position: relative;
            box-sizing: border-box;
        }
        #product_images_container .image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
    <?php
}
//Backend code for product meta box - admin side
/////END/////

function ams_admin_styles() {
    echo '<style>
        .ams-update-notice {
            background-color: #fff8e5;
            border-left: 4px solid #ffb900;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            margin: 10px 0;
            padding: 12px 15px;
        }
        .ams-update-notice strong {
            display: block;
            margin-bottom: 8px;
            color: #23282d;
        }
        .ams-update-notice a {
            color: #0073aa;
            text-decoration: none;
            font-weight: 500;
        }
        .ams-update-notice a:hover {
            color: #00a0d2;
            text-decoration: underline;
        }
    </style>';
}

function ams_add_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-product-setting-page&tab=pills-general-tab') . '">' . __('Settings', 'ams-wc-amazon') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

function ams_plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status) {
    if (plugin_basename(AMS_PLUGIN_FILE) == $plugin_file) {
        $row_meta = array(
            'docs'    => '<a href="' . esc_url('knowledge-base/') . '" target="_blank" aria-label="' . esc_attr__('Documentation', 'ams-wc-amazon') . '">' . esc_html__('Documentation', 'ams-wc-amazon') . '</a>',
            'support' => '<a href="' . esc_url('support/') . '" target="_blank" aria-label="' . esc_attr__('Support', 'ams-wc-amazon') . '">' . esc_html__('Support', 'ams-wc-amazon') . '</a>',
            'demo'    => '<a href="' . esc_url('woocommerce-amazon-free-demo/') . '" target="_blank" aria-label="' . esc_attr__('Demo', 'ams-wc-amazon') . '">' . esc_html__('Demo', 'ams-wc-amazon') . '</a>',
            'view_details' => '<a href="' . self_admin_url('plugin-install.php?tab=plugin-information&plugin=ams-wc-amazon&TB_iframe=true&width=772&height=563') . '" class="thickbox open-plugin-details-modal" aria-label="' . esc_attr__('View  details', 'ams-wc-amazon') . '">' . esc_html__('View details', 'ams-wc-amazon') . '</a>'
        );
        $plugin_meta = array_merge($plugin_meta, $row_meta);

        $new_version = ams_check_version();
        if ($new_version && version_compare(AMS_PLUGIN_VERSION, $new_version, '<')) {
            $update_url = 'https://codecanyon.net/downloads/';
            $details_url = 'woocommerce-amazon-logs/';
            
            $update_notice = sprintf(
                '<div class="ams-update-notice">
                    <strong>%s</strong>
                    %s %s is available. 
                    <a href="%s" target="_blank">View version %s details</a> 
                    or 
                    <a href="%s" target="_blank">update now</a>.
                </div>',
                esc_html__('Update Available', 'ams-wc-amazon'),
                esc_html__('A new version of', 'ams-wc-amazon'),
                AMS_PLUGIN_NAME,
                esc_url($details_url),
                esc_html($new_version),
                esc_url($update_url)
            );
            
            $plugin_meta[] = $update_notice;
        }
    }
    return $plugin_meta;
}

///debug code///
// Debug function for variable product (variations)
function display_variation_image_debug_info() {
    global $product;
    if (!is_a($product, 'WC_Product_Variable') || !current_user_can('manage_options')) {
        return;
    }
    
    $variations = $product->get_available_variations();
    echo '<div id="variation-image-debug" style="margin-top: 50px; padding: 20px; background: #f0f0f0; border: 1px solid #ddd;">';
    echo '<h3>Variation Image Debug Information</h3>';
    
    foreach ($variations as $variation) {
        $variation_id = $variation['variation_id'];
        $variation_obj = wc_get_product($variation_id);
        echo '<div style="margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">';
        echo '<h4>Variation ID: ' . $variation_id . '</h4>';
        
        // Display variation attributes
        echo '<p>Attributes: ';
        foreach ($variation['attributes'] as $attribute => $value) {
            echo $attribute . ': ' . $value . ', ';
        }
        echo '</p>';
        
        // Display main image information
        $image_id = $variation_obj->get_image_id();
        $image_url = wp_get_attachment_image_url($image_id, 'full');
        echo '<p>Main Image ID: ' . $image_id . '</p>';
        echo '<p>Main Image URL: ' . ($image_url ? $image_url : 'No image set') . '</p>';
        if ($image_url) {
            echo '<img src="' . $image_url . '" style="max-width: 100px; height: auto;" />';
        }

        // Display gallery image information
        $gallery_image_ids = $variation_obj->get_gallery_image_ids();
        echo '<p>Gallery Image IDs: ' . implode(', ', $gallery_image_ids) . '</p>';
        foreach ($gallery_image_ids as $gallery_image_id) {
            $gallery_image_url = wp_get_attachment_image_url($gallery_image_id, 'full');
            echo '<p>Gallery Image URL (Attachment ID ' . $gallery_image_id . '): ' . ($gallery_image_url ? $gallery_image_url : 'No image found') . '</p>';
            if ($gallery_image_url) {
                echo '<img src="' . $gallery_image_url . '" style="max-width: 100px; height: auto;" />';
            }
        }

        // Check for remote URLs
        $all_image_urls = get_post_meta($variation_id, '_url', true);
        echo '<h5>All Saved Remote Image URLs:</h5>';
        if (!empty($all_image_urls)) {
            echo '<p>Remote Image URL: ' . $all_image_urls . '</p>';
            echo '<img src="' . $all_image_urls . '" style="max-width: 100px; height: auto;" />';
        } else {
            echo '<p>No remote image URLs found.</p>';
        }

        echo '</div>';
    }
    echo '</div>';
}

// Debug function for simple product
function display_simple_product_debug_info() {
    global $product;
    if (!is_a($product, 'WC_Product_Simple') || !current_user_can('manage_options')) {
        return;
    }
    
    echo '<div id="simple-product-debug" style="margin-top: 50px; padding: 20px; background: #f0f0f0; border: 1px solid #ddd;">';
    echo '<h3>Simple Product Debug Information</h3>';
    
    // Display product ID
    echo '<p>Product ID: ' . $product->get_id() . '</p>';
    
    // Display price
    echo '<p>Price: ' . $product->get_price() . '</p>';
    
    // Display stock status
    echo '<p>Stock Status: ' . $product->get_stock_status() . '</p>';
    
    // Display main image information
    $image_id = $product->get_image_id();
    $image_url = wp_get_attachment_image_url($image_id, 'full');
    echo '<p>Main Image ID: ' . $image_id . '</p>';
    echo '<p>Main Image URL: ' . ($image_url ? $image_url : 'No image set') . '</p>';
    if ($image_url) {
        echo '<img src="' . $image_url . '" style="max-width: 100px; height: auto;" />';
    }
    
    // Display gallery image information
    $gallery_image_ids = $product->get_gallery_image_ids();
    echo '<p>Gallery Image IDs: ' . implode(', ', $gallery_image_ids) . '</p>';
    foreach ($gallery_image_ids as $gallery_image_id) {
        $gallery_image_url = wp_get_attachment_image_url($gallery_image_id, 'full');
        echo '<p>Gallery Image URL (Attachment ID ' . $gallery_image_id . '): ' . ($gallery_image_url ? $gallery_image_url : 'No image found') . '</p>';
        if ($gallery_image_url) {
            echo '<img src="' . $gallery_image_url . '" style="max-width: 100px; height: auto;" />';
        }
    }

    // Check for remote URLs
    $remote_image_url = get_post_meta($product->get_id(), '_url', true);
    echo '<h5>Remote Image URL:</h5>';
    if ($remote_image_url) {
        echo '<p>' . $remote_image_url . '</p>';
        echo '<img src="' . $remote_image_url . '" style="max-width: 100px; height: auto;" />';
    } else {
        echo '<p>No remote image URLs found.</p>';
    }

    echo '</div>';
}
///debug code///



//Aggressively remove external admin notices on AMS admin pages.
//Define the AMS plugin pages slugs.
function ams_get_allowed_pages() {
    return array(
        'wc-amazon-affiliate',
        'product-review-import',
        'wc-product-search',
        'products-search-without-api',
        'product-import-by-url',
        'wc-product-setting-page',
        'view-logs'
    );
}

//Helper function: Check if the current admin page is one of our AMS pages.
function ams_is_our_plugin_page() {
    if ( ! is_admin() ) {
        return false;
    }
    if ( isset( $_GET['page'] ) && in_array( $_GET['page'], ams_get_allowed_pages(), true ) ) {
        return true;
    }
    return false;
}

//1. Remove notice callbacks from standard hooks on AMS admin pages.
function ams_remove_admin_notice_callbacks() {
    if ( ! ams_is_our_plugin_page() ) {
        return;
    }
    global $wp_filter;
    if ( isset( $wp_filter['admin_notices']->callbacks ) ) {
        $wp_filter['admin_notices']->callbacks = array();
    }
    if ( isset( $wp_filter['all_admin_notices']->callbacks ) ) {
        $wp_filter['all_admin_notices']->callbacks = array();
    }
    if ( isset( $wp_filter['network_admin_notices']->callbacks ) ) {
        $wp_filter['network_admin_notices']->callbacks = array();
    }
}
add_action( 'current_screen', 'ams_remove_admin_notice_callbacks' );

//2. Start output buffering to filter out notice HTML on AMS admin pages.
function ams_start_output_buffer() {
    if ( ams_is_our_plugin_page() ) {
        ob_start( 'ams_output_buffer_callback' );
    }
}
function ams_output_buffer_callback( $buffer ) {
    // This regex removes any <div> with common notice classes.
    $pattern = '/<div[^>]+class=["\'][^"\']*(notice|updated|error|is-dismissible)[^"\']*["\'][^>]*>.*?<\/div>/is';
    return preg_replace( $pattern, '', $buffer );
}
add_action( 'admin_init', 'ams_start_output_buffer', 0 );

//3. CSS fallback to hide notice elements on AMS admin pages.
function ams_hide_notices_css() {
    if ( ams_is_our_plugin_page() ) {
        echo '<style>
            .notice, .updated, .error, .is-dismissible { display: none !important; }
        </style>';
    }
}
add_action( 'admin_head', 'ams_hide_notices_css' );

//4. JavaScript fallback to remove notice elements after page load on AMS admin pages.
function ams_remove_notices_js() {
    if ( ams_is_our_plugin_page() ) {
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.notice, .updated, .error, .is-dismissible').remove();
        });
        </script>
        <?php
    }
}
add_action( 'admin_footer', 'ams_remove_notices_js' );
//Aggressively remove external admin notices on AMS admin pages.


function clean_completed_woocommerce_actions() {
    if (get_option('enable_clean_completed_actions') !== '1') {
        return;
    }

    global $wpdb;
    $wpdb->query("
        DELETE FROM {$wpdb->prefix}actionscheduler_actions
        WHERE status = 'complete'
        AND hook IN (
            'wc_delete_related_product_transients_async',
            'woocommerce_run_product_attribute_lookup_update_callback',
            'woocommerce_cleanup_draft_orders'
        )
    ");
}

function clean_all_actionscheduler_logs() {
    if (get_option('enable_clean_action_logs') !== '1') {
        return;
    }

    global $wpdb;
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}actionscheduler_logs");
}
