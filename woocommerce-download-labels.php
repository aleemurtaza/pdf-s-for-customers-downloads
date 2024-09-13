<?php
/**
 * Plugin Name: WooCommerce Download Labels
 * Description: Adds a "Download Labels" tab to the WooCommerce My Account page with PDF file downloads and allows admins to upload files, set custom headings, and manage uploaded files.
 * Version: 1.2
 * Author: Alee Murtaza
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add custom endpoint
function wdl_add_download_labels_endpoint() {
    add_rewrite_endpoint( 'download-labels', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'wdl_add_download_labels_endpoint' );

// Add new tab to My Account menu
function wdl_add_download_labels_link_my_account( $items ) {
    $items['download-labels'] = __( 'Download Labels', 'woocommerce-download-labels' );
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'wdl_add_download_labels_link_my_account' );

// Content for the new endpoint
function wdl_download_labels_content() {
    $pdf_files = get_option( 'wdl_pdf_files', array() );
    $label_headings = get_option( 'wdl_label_headings', array() );

    echo '<h3 class="wdl-heading">' . __( 'Download Labels', 'woocommerce-download-labels' ) . '</h3>';
    echo '<p>' . __( 'Click on the links below to download the PDF labels.', 'woocommerce-download-labels' ) . '</p>';

    if ( ! empty( $pdf_files ) ) {
        foreach ( $pdf_files as $key => $url ) {
            $heading = isset( $label_headings[ $key ] ) ? esc_html( $label_headings[ $key ] ) : 'Label ' . ( $key + 1 );
            echo '<div class="wdl-label"><a href="' . esc_url( $url ) . '" download>' . $heading . '</a></div>';
        }
    } else {
        _e( 'No labels available at the moment.', 'woocommerce-download-labels' );
    }
}
add_action( 'woocommerce_account_download-labels_endpoint', 'wdl_download_labels_content' );

// Add styles to the customer side
function wdl_customer_css() {
    echo '<style>
        .wdl-heading {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .wdl-label {
            margin-bottom: 10px;
        }
        .wdl-label a {
            font-size: 18px;
            color: #0073aa;
            text-decoration: none;
        }
        .wdl-label a:hover {
            color: #005177;
            text-decoration: underline;
        }
    </style>';
}
add_action( 'wp_head', 'wdl_customer_css' );

// Create admin menu
function wdl_admin_menu() {
    add_menu_page(
        __( 'Add Labels', 'woocommerce-download-labels' ),
        __( 'Add Labels', 'woocommerce-download-labels' ),
        'manage_options',
        'wdl-download-labels',
        'wdl_admin_page',
        'dashicons-admin-generic',
        56
    );
}
add_action( 'admin_menu', 'wdl_admin_menu' );

// Admin page content
function wdl_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Add Labels', 'woocommerce-download-labels' ); ?></h1>
        <form method="post" action="options.php" enctype="multipart/form-data">
            <?php
            settings_fields( 'wdl_settings' );
            do_settings_sections( 'wdl-download-labels' );
            submit_button();
            ?>
            <button type="button" id="wdl-clear-labels" class="button"><?php _e( 'Clear All Labels', 'woocommerce-download-labels' ); ?></button>
        </form>
    </div>
    <?php
}

// Register settings and fields
function wdl_admin_settings() {
    register_setting(
        'wdl_settings',
        'wdl_pdf_files',
        array(
            'sanitize_callback' => 'wdl_sanitize_pdf_files'
        )
    );

    register_setting(
        'wdl_settings',
        'wdl_label_headings',
        array(
            'sanitize_callback' => 'wdl_sanitize_label_headings'
        )
    );

    add_settings_section(
        'wdl_main_section',
        __( 'Manage PDF Labels', 'woocommerce-download-labels' ),
        null,
        'wdl-download-labels'
    );

    add_settings_field(
        'wdl_pdf_file_1',
        __( 'Upload PDF File 1', 'woocommerce-download-labels' ),
        'wdl_pdf_file_1_field_cb',
        'wdl-download-labels',
        'wdl_main_section'
    );

    add_settings_field(
        'wdl_label_heading_1',
        __( 'Label Heading 1', 'woocommerce-download-labels' ),
        'wdl_label_heading_1_field_cb',
        'wdl-download-labels',
        'wdl_main_section'
    );

    add_settings_field(
        'wdl_pdf_file_2',
        __( 'Upload PDF File 2', 'woocommerce-download-labels' ),
        'wdl_pdf_file_2_field_cb',
        'wdl-download-labels',
        'wdl_main_section'
    );

    add_settings_field(
        'wdl_label_heading_2',
        __( 'Label Heading 2', 'woocommerce-download-labels' ),
        'wdl_label_heading_2_field_cb',
        'wdl-download-labels',
        'wdl_main_section'
    );
}
add_action( 'admin_init', 'wdl_admin_settings' );

// Callback for first PDF file field
function wdl_pdf_file_1_field_cb() {
    $pdf_files = get_option( 'wdl_pdf_files', array() );
    $pdf_file_1 = isset( $pdf_files['File 1'] ) ? $pdf_files['File 1'] : '';
    ?>
    <input type="file" name="wdl_pdf_file_1" />
    <?php if ( $pdf_file_1 ) : ?>
        <p><?php echo __( 'Current file:', 'woocommerce-download-labels' ); ?> <a href="<?php echo esc_url( $pdf_file_1 ); ?>" target="_blank"><?php echo esc_html( basename( $pdf_file_1 ) ); ?></a> <button type="button" class="button wdl-remove-file" data-file="File 1"><?php _e( 'Remove', 'woocommerce-download-labels' ); ?></button></p>
    <?php endif; ?>
    <?php
}

// Callback for first label heading field
function wdl_label_heading_1_field_cb() {
    $label_headings = get_option( 'wdl_label_headings', array() );
    $label_heading_1 = isset( $label_headings['File 1'] ) ? $label_headings['File 1'] : '';
    ?>
    <input type="text" name="wdl_label_headings[File 1]" value="<?php echo esc_attr( $label_heading_1 ); ?>" placeholder="<?php _e( 'Enter label heading', 'woocommerce-download-labels' ); ?>" />
    <?php
}

// Callback for second PDF file field
function wdl_pdf_file_2_field_cb() {
    $pdf_files = get_option( 'wdl_pdf_files', array() );
    $pdf_file_2 = isset( $pdf_files['File 2'] ) ? $pdf_files['File 2'] : '';
    ?>
    <input type="file" name="wdl_pdf_file_2" />
    <?php if ( $pdf_file_2 ) : ?>
        <p><?php echo __( 'Current file:', 'woocommerce-download-labels' ); ?> <a href="<?php echo esc_url( $pdf_file_2 ); ?>" target="_blank"><?php echo esc_html( basename( $pdf_file_2 ) ); ?></a> <button type="button" class="button wdl-remove-file" data-file="File 2"><?php _e( 'Remove', 'woocommerce-download-labels' ); ?></button></p>
    <?php endif; ?>
    <?php
}

// Callback for second label heading field
function wdl_label_heading_2_field_cb() {
    $label_headings = get_option( 'wdl_label_headings', array() );
    $label_heading_2 = isset( $label_headings['File 2'] ) ? $label_headings['File 2'] : '';
    ?>
    <input type="text" name="wdl_label_headings[File 2]" value="<?php echo esc_attr( $label_heading_2 ); ?>" placeholder="<?php _e( 'Enter label heading', 'woocommerce-download-labels' ); ?>" />
    <?php
}

// Sanitize and save PDF files
function wdl_sanitize_pdf_files( $input ) {
    $output = array();
    
    if ( isset( $_FILES['wdl_pdf_file_1'] ) && $_FILES['wdl_pdf_file_1']['size'] > 0 ) {
        $upload = wp_handle_upload( $_FILES['wdl_pdf_file_1'], array( 'test_form' => false ) );
        if ( isset( $upload['url'] ) ) {
            $output['File 1'] = esc_url( $upload['url'] );
        }
    } else {
        $output['File 1'] = isset( $input['File 1'] ) ? esc_url( $input['File 1'] ) : '';
    }

    if ( isset( $_FILES['wdl_pdf_file_2'] ) && $_FILES['wdl_pdf_file_2']['size'] > 0 ) {
        $upload = wp_handle_upload( $_FILES['wdl_pdf_file_2'], array( 'test_form' => false ) );
        if ( isset( $upload['url'] ) ) {
            $output['File 2'] = esc_url( $upload['url'] );
        }
    } else {
        $output['File 2'] = isset( $input['File 2'] ) ? esc_url( $input['File 2'] ) : '';
    }

    return $output;
}

// Sanitize and save label headings
function wdl_sanitize_label_headings( $input ) {
    $output = array();

    if ( isset( $input['File 1'] ) ) {
        $output['File 1'] = sanitize_text_field( $input['File 1'] );
    }

    if ( isset( $input['File 2'] ) ) {
        $output['File 2'] = sanitize_text_field( $input['File 2'] );
    }

    return $output;
}

// Add styles to the admin side
function wdl_admin_css() {
    echo '<style>
        .form-table th {
            width: 200px;
        }
        .form-table td input[type="text"] {
            width: 300px;
        }
        .wdl-remove-file {
            margin-left: 10px;
            color: red;
            cursor: pointer;
        }
        #wdl-clear-labels {
            margin-top: 20px;
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>';
}
add_action( 'admin_head', 'wdl_admin_css' );

// Handle file removal
function wdl_remove_file() {
    $file_key = isset( $_POST['file_key'] ) ? sanitize_text_field( $_POST['file_key'] ) : '';

    if ( $file_key ) {
        $pdf_files = get_option( 'wdl_pdf_files', array() );
        $label_headings = get_option( 'wdl_label_headings', array() );

        if ( isset( $pdf_files[ $file_key ] ) ) {
            unset( $pdf_files[ $file_key ] );
        }

        if ( isset( $label_headings[ $file_key ] ) ) {
            unset( $label_headings[ $file_key ] );
        }

        update_option( 'wdl_pdf_files', $pdf_files );
        update_option( 'wdl_label_headings', $label_headings );
    }

    wp_send_json_success();
}
add_action( 'wp_ajax_wdl_remove_file', 'wdl_remove_file' );

// Handle clearing all labels
function wdl_clear_all_labels() {
    update_option( 'wdl_pdf_files', array() );
    update_option( 'wdl_label_headings', array() );

    wp_send_json_success();
}
add_action( 'wp_ajax_wdl_clear_all_labels', 'wdl_clear_all_labels' );

// JavaScript for handling file removal and clearing all labels
function wdl_admin_scripts() {
    echo '<script>
        jQuery(document).ready(function($) {
            $(".wdl-remove-file").click(function() {
                var fileKey = $(this).data("file");

                if (confirm("Are you sure you want to remove this file?")) {
                    $.post(ajaxurl, {
                        action: "wdl_remove_file",
                        file_key: fileKey
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                }
            });

            $("#wdl-clear-labels").click(function() {
                if (confirm("Are you sure you want to clear all labels?")) {
                    $.post(ajaxurl, {
                        action: "wdl_clear_all_labels"
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>';
}
add_action( 'admin_footer', 'wdl_admin_scripts' );

?>
