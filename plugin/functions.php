// USPs voorraadstatus + waar te zien
function emnl_legacy_usps_uniliving_stock_info() {

    // Setting (messy) variables

        // Get the Woocommerce product ID
        $product_id = get_the_ID();
        
        // Checking if there are SubSKU repeater rows in the product
        if ( have_rows ( 'uniliving_artikelen', $product_id ) ) {

                $currentTime = time();
                $publicationTime = get_the_time ( 'U' );
                $hoursAfterPublication = ( $currentTime - $publicationTime ) / 3600;

                $voorraadwinkel = 0;
                $voorraadmagazijn = 0;
                $voorraadoutlet = 0;
                $voorraadstd = 0;
                $verkocht = 0;
                $minvoorraad = 0;
                $i = 0;
                $commissiecode = 1;
                $opvoorraad_std = 1;
                $opvoorraad_winkel = 1;

                $icon_op_voorraad = 1;

                $uitverkocht = 1;
                $no_icon = 1;
                $icon_nog_maar = 1;
                $icon_beperkt = 0;
                $isset_collectie = 1;
                $beperkt = 1;
                $min_voorraad_set = 0;
                $subFieldWithDescription = 0;

                if ( have_rows ('uniliving_artikelen' ) ):
                    while ( have_rows ( 'uniliving_artikelen' ) ) : the_row();
                        if ( $hoursAfterPublication < 6 ) {
                            if ( get_sub_field('omschrijving' ) == '' &&
                                    get_sub_field('voorraad_winkel') == '' &&
                                    get_sub_field('voorraad_magazijn') == '' &&
                                    get_sub_field('voorraad_outlet') == '' &&
                                    get_sub_field('voorraad_std') == '' &&
                                    get_sub_field('min_voorraad') == '' &&
                                    get_sub_field('verkoopprijs') == '' &&
                                    get_sub_field('commissie') == '' &&
                                    get_sub_field('collectie') == '' &&
                                    get_sub_field('verkocht') == '') {
                            } else {
                                $hideClass = '';
                            }
                        }
                        else {
                            if (get_sub_field('omschrijving') == 'CSV older than 24 hours'){
                                $hideClass = 'hideIcons';
                            }
                            else {
                                $hideClass = '';
                            }
                        }

                        if (get_sub_field('omschrijving') && get_sub_field('omschrijving') != '') {
                            $subFieldWithDescription = 1;
                            // display a sub field value
                            // the_sub_field('artnr');
                            $voorraadwinkel += get_sub_field('voorraad_winkel');
                            $voorraadmagazijn += get_sub_field('voorraad_magazijn');
                            $voorraadoutlet += get_sub_field('voorraad_outlet');
                            $voorraadstd += get_sub_field('voorraad_std');
                            if ( get_sub_field ('min_voorraad' ) ) { $minvoorraad += get_sub_field ( 'min_voorraad' ); }
                            $i++;
                            $collectie = get_sub_field('collectie');

                            // fallible Uitverkocht
                            if (!isset($collectie) || $collectie == "") {
                                $isset_collectie = 0;
                                $uitverkocht = 0;
                            }
                            if (get_sub_field('voorraad_winkel') > 0 || get_sub_field('voorraad_magazijn') > 0 || get_sub_field('voorraad_outlet') > 0) {
                                $uitverkocht = 0;
                            }
                            if (!isset($collectie) || $collectie == "") {
                                $min_voorraad_set = 1;
                                if (get_sub_field('min_voorraad') && get_sub_field('min_voorraad') != "") {

                                    if (get_sub_field('voorraad_std') > 0) {
                                        if (get_sub_field('voorraad_std') < get_sub_field('min_voorraad')) {
                                            $icon_op_voorraad = 0;
                                        }
                                    } else {
                                        $icon_op_voorraad = 0;
                                    }
                                } else {
                                    $icon_op_voorraad = 0;
                                }
                            }
                            if (get_sub_field('min_voorraad') && get_sub_field('min_voorraad') != "" && get_sub_field('voorraad_std') > 0) {
                                $icon_beperkt = 1;
                            }
                            if (get_sub_field('voorraad_magazijn') || get_sub_field('voorraad_magazijn') != "") {
                                $no_icon = 0;
                            }
                            // fallible nog maar X !

                            if (get_sub_field('commissie') == 2) {
                                if ($voorraadwinkel + $voorraadmagazijn + $voorraadoutlet > 0) {
                                    $icon_nog_maar = 1;
                                } else {
                                    $icon_nog_maar = 0;
                                }
                            }
                            if (get_sub_field('commissie') != 2) {
                                $commissiecode = 0;
                            }
                            if (get_sub_field('voorraad_std') <= 0) {
                                $voorraad_std = 0;
                            }
                            if (get_sub_field('verkoopprijs') <= 0 || get_sub_field('commissie') != 2 || get_sub_field('voorraad_std') <= 0) {
                                $opvoorraad = 0;
                            }
                        }
                    endwhile;
                else :
                    // no rows found
                    $hideClass = '';
                    $commissiecode = 0;
                    $std_code = 0;
                    $verkoopprijs = 0;
                    $no_icon = 2;

                endif;

                if ($subFieldWithDescription == 0) {
                    // no rows found with description
                    // but if publication datum < 6??
                    $hideClass = '';
                    $commissiecode = 0;
                    $std_code = 0;
                    $verkoopprijs = 0;
                    $no_icon = 2;
                    if ($hoursAfterPublication < 6) {
                        if (
                            get_sub_field('omschrijving') == ''
                            && get_sub_field('voorraad_winkel') == ''
                            && get_sub_field('voorraad_magazijn') == ''
                            && get_sub_field('voorraad_outlet') == ''
                            && get_sub_field('voorraad_std') == ''
                            && get_sub_field('min_voorraad') == ''
                            && get_sub_field('verkoopprijs') == ''
                            && get_sub_field('commissie') == ''
                            && get_sub_field('collectie') == ''
                            && get_sub_field('verkocht') == ''
                        ) {
                            $hideClass = 'hideIcons';

                        }

                    }

                }

                $voorraad = $voorraadwinkel + $voorraadmagazijn + $voorraadoutlet;

        }
        
    // Calculate the USPs

        if ( ! emnl_legacy_check_outdated_data ( $product_id ) ) {

            // USP 1: voorraadstatus

                if ( $no_icon != 2 && $isset_collectie == 1 && $icon_nog_maar == 1 ) {
                    $usp_line_1 = '<span class="red">Nog Maar ' . $voorraad . '!</span> Wees snel want <span class="red">WEG=PECH!</span>';
                }
                
                elseif ( $min_voorraad_set == 1 && $icon_op_voorraad == 1) {

                    // Getting Shipping Class Name
                    $_product = wc_get_product( $product_id );
                    $shipping_class_id = $_product->get_shipping_class_id();
                    $shipping_class_term = get_term_by ('id', $shipping_class_id, 'product_shipping_class');

                    if ( is_object ( $shipping_class_term ) ) {
                        $shipping_class_name = $shipping_class_term->name;
                    }

                    $usp_line_1 = '<span class="blue">Op voorraad.</span> ';

                    // Checking if product is shipped via pakketpost
                    if ( isset ( $shipping_class_name ) && strpos ( $shipping_class_name, 'Pakketpost: JA') !== false) {
                        $usp_line_1 .= 'Binnen 3 werkdagen in huis';
                    } else {
                        $usp_line_1 .= 'Binnen 2 weken in huis';
                    }

                } elseif ( $icon_beperkt == 1 ) {
                    $usp_line_1 = '<span class="blue">Beperkt op voorraad!</span> Houd rekening met levertijd';
                } elseif ( $no_icon != 2 && $uitverkocht == 1 ) {
                    $usp_line_1 = '<span class="red">Uitverkocht!</span> Wellicht hebben we een alternatief?';
                } elseif ( $no_icon == 0 || $no_icon == 2) {
                    $usp_line_1 = '<span class="blue">Op bestelling</span>. Houd rekening met levertijd!';
                }

            // USP 2: waar te zien

                if ( $voorraadwinkel > 0  ) {
                    $usp_line_2 = '<span class="blue">In showroom.</span> Te bekijken in onze winkel';
                } elseif ( $voorraadoutlet > 0  ) {
                    $usp_line_2 = '<span class="red">In outlet.</span> Kom kijken bij onze pop-up outlet';
                } elseif ( $voorraadmagazijn > 0  ) {
                    $usp_line_2 = '<span class="blue">In magazijn.</span> U kunt <span class="blue">op afspraak</span> komen kijken';
                } elseif ( $no_icon == 0 || $no_icon == 2) {
                    $usp_line_2 = '<span class="red">Momenteel niet in onze showroom!</span>';
                }

        }

    // Return the USPs

        if ( isset ( $usp_line_1 ) ) {
            $usp_line = emnl_usp_line_container ( $usp_line_1 );
        }

        if ( isset ( $usp_line_2 ) ) { 
            $usp_line = isset ( $usp_line ) ? $usp_line . emnl_usp_line_container ( $usp_line_2 ) : emnl_usp_line_container ( $usp_line_2 );
        }

        if ( isset ( $usp_line ) ) {
            return $usp_line;
        } else {
            return false;
        }

}


// Support function to check if ACF repeater rows contain recent data
function emnl_legacy_check_outdated_data ( $product_id ) {

    // Assuming there is no outdated tagline found
    $outdated_tagline_found = false;
    
    // Checking if the product has ACF repeater rows
    if ( have_rows ( 'uniliving_artikelen', $product_id ) ) {
        
        // We assume the tagline is not here
        $outdated_tagline_found = false;

        // Checking all the rows until done OR the outdated tagline is found
        $outdated_tagline = 'CSV older than 24 hours';
        $rows = get_field( 'uniliving_artikelen' );

        if ( $rows ) {
            
            foreach ( $rows as $row ) {

                if ( strpos ( $row['omschrijving'], $outdated_tagline ) !== false ) {
                    
                    // Outdated tagline is found! Stop the presses!
                    $outdated_tagline_found = true;
                    break;
    
                }

            }

            // Remove the reference at which record of the array loop ended
            unset ($row);

        }

    }

    // Returning result
    return $outdated_tagline_found;

}


// Wrap each USP line in a HTML container
function emnl_usp_line_container ( $usp_line, $hideClass  = '' ) {
    
    $usp_line_container = '<div class="single-product-icon blue' . $hideClass . '">';
    $usp_line_container .= '<i class="fa fa-check-circle"></i>';
    $usp_line_container .= '<p>' . $usp_line . '</p>';
    $usp_line_container .= '</div>';

    return $usp_line_container;

}


// Shortcode function to return text line when Uniliving stock status was last updated (voorraad bijgewerkt)
function emnl_legacy_voorraad_bijgewerkt () {

    // Checking if plugins ACF and WooCommerce are active
    if ( ! function_exists ( 'have_rows' ) || ! function_exists ( 'is_product' ) ) {
        return;
    }

    // Checking if viewing a WooCommerce product
    if ( !is_product () ) {
        return;
    }

    // Get the Woocommerce product ID
    $product_id = get_the_ID();

    if ( ! emnl_legacy_check_outdated_data ( $product_id ) ) {

        // Checking if there are SubSKU repeater rows in the product
        if ( have_rows ( 'uniliving_artikelen', $product_id ) ) {

            // Loop for creating an array with the update timestamps
            while ( have_rows ( 'uniliving_artikelen', $product_id ) ) : the_row ();

                // Get the description (Omschrijving)
                $omschrijving_subsku = get_sub_field( 'omschrijving' );

                // Only get the datetime of updated which is between "(Updated" and ")"
                if ( preg_match ( '/[(]Updated (.*?)[)]/', $omschrijving_subsku ) ) {

                    preg_match ( '/[(]Updated (.*?)[)]/', $omschrijving_subsku, $datetime );
                    
                    // Convert the formatted datetime to a timestamp
                    $datetime_stamp = strtotime ( $datetime[1] );

                    // Checking if the outcome is really a timestamp
                    if ( is_numeric ( strtotime ( $datetime[1] ) ) ) {
                        
                        // Add timestamp to array
                        $datetime_stamp_array[] = $datetime_stamp;

                    }
                
                }

            endwhile;
                
            if ( isset ( $datetime_stamp_array ) ) {
                
                // Checking for the earliest updated timestamp
                $first_updated = min ( $datetime_stamp_array );

                // Converting timestamp back to a formatted date
                $first_updated_formatted = date ( "d-m-Y G:i",  $first_updated );

                // Returning shortcode in HTML
                return '<p></i><span class="voorraad-bijgewerkt">Voorraadgegevens laatst bijgewerkt op ' . $first_updated_formatted . ' en onder voorbehoud.</span></p>';

            }
        
        }

    }

}


// Shortcode function to show stock amount of standaardmagazijn
function emnl_legacy_voorraadstd ( $atts ) {

    // Validate the argument
    if ( !isset ( $atts['artnr'] ) ) {
        return;
    }

    // Validate the argument
    if ( !is_numeric ( $atts['artnr'] ) ) {
        return;
    }

    // Checking if plugins ACF and WooCommerce are active
    if ( !function_exists ( 'have_rows' ) || !function_exists ( 'is_product' ) ) {
        return;
    }

    // Get the Woocommerce product ID
    $product_id = get_the_ID();

    // Checking if there is no outdated data
    if ( ! emnl_legacy_check_outdated_data ( $product_id ) ) {

        // Checking if the product has ACF repeater rows
        if ( have_rows ( 'uniliving_artikelen', $product_id = '' ) ) {

            // We have not found the tagline until proved (found!) otherwise
            $artnr_found = false;

            // Checking all the rows until done OR the outdated tagline is found
            $rows = get_field( 'uniliving_artikelen' );

            if ( $rows ) {
    
                foreach ( $rows as $row ) {
    
                    if ( $row['artnr'] == $atts['artnr'] && $row['omschrijving'] ) {
    
                        // Product is found! Stop the presses!
                        $voorraad = '<span class="voorraad-aantal">(op voorraad: ';
                        $voorraad .= $row['voorraad_std'];
                        $voorraad .= ')</span><br>';
                        $artnr_found = true;
                        break;
        
                    }
    
                }
    
            }

            // If there was a match return HTML container
            if ( isset ( $voorraad ) ) {

                return $voorraad;

            }

        }

    }

}
