<?php
/*
Plugin Name: Floating Call to Action Button
Description: Adds a floating call to action button with customizable options.
Version: 2.1
Author: Weaverbird Web
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class FloatingCTAButton {

    // Constructor function to hook into WordPress actions
    public function __construct() {
        // Add a menu item in the admin panel
        add_action('admin_menu', array($this, 'create_admin_page'));
        // Set up settings sections
        add_action('admin_init', array($this, 'setup_sections'));
        // Set up settings fields
        add_action('admin_init', array($this, 'setup_fields'));
        // Enqueue necessary scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        // Display the CTA button in the footer
        add_action('wp_footer', array($this, 'display_button'));
        // Add google fonts
        add_action( 'wp_enqueue_scripts', array($this, 'add_google_font') );
        // New hook for admin notices
        add_action('admin_notices', array($this, 'display_admin_notices'));

        // Admin page styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    // Function to create an admin page under the WordPress settings menu
    public function create_admin_page() {
        add_menu_page(
            'Floating CTA', // Page title
            'Floating CTA ', // Menu title
            'manage_options', // Capability required
            'floating_cta_button', // Menu slug
            array($this, 'admin_page_content'), // Callback function to display content
            'dashicons-megaphone', // Icon
            100 // Position in the menu
        );
    }

    // Function to display the content of the admin page
    public function admin_page_content() {
        ?>
        <div class="wrap floating-cta-button-settings">
            <h1>Floating Call to Action</h1>
            <form method="post" action="options.php">
                <?php
                // Output settings fields and sections
                settings_fields('floating_cta_button');
                do_settings_sections('floating_cta_button');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function display_admin_notices() {
        // Check for success message
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            echo '<div class="notice notice-success is-dismissible">
                <p>Settings have been updated successfully.</p>
            </div>';
        }
    
        // Check for test error message
        if (isset($_GET['test_error']) && $_GET['test_error'] === '1') {
            echo '<div class="notice notice-error is-dismissible">
                <p>This is a test error message. Something went wrong!</p>
            </div>';
        }
    }
    
    // Function to set up sections on the settings page
    public function setup_sections() {
        add_settings_section(
            'floating_cta_button_section', // Section ID
            'Button Settings', // Title
            array(), // Callback function (none here)
            'floating_cta_button' // Page slug
        );
    }

    // Enqueue the admin CSS only on the settings page
    public function enqueue_admin_styles($hook) {
        // Check if we're on the plugin settings page
        if ($hook == 'toplevel_page_floating_cta_button') {
            wp_enqueue_style('floating-cta-button-admin', plugins_url('/css/admin-style.css', __FILE__));
        }
    }
    

    // Function to set up fields on the settings page
    public function setup_fields() {
        $fields = array(
            // Enable or disable the CTA button
            array(
                'uid' => 'floating_cta_button_enabled',
                'label' => 'Enable CTA Button',
                'section' => 'floating_cta_button_section',
                'type' => 'checkbox',
                'helper' => 'Check to enable the CTA button.',
                'supplemental' => '',
            ),

            // Pages to display the button on
            array(
                'uid' => 'floating_cta_button_pages',
                'label' => 'Pages to Display',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => $this->get_pages(), // Dynamically generate the list of pages
                'multiple' => true,
                'placeholder' => 'Select Pages',
                'helper' => 'Select pages where the button should be displayed.',
                'supplemental' => '',
            ),

            // Button text
            array(
                'uid' => 'floating_cta_button_text',
                'label' => 'Button Text',
                'section' => 'floating_cta_button_section',
                'type' => 'text',
                'helper' => 'Enter the button Text',
                'supplemental' => '',
            ),
            
            // Button link
            array(
                'uid' => 'floating_cta_button_link',
                'label' => 'Button Link',
                'section' => 'floating_cta_button_section',
                'type' => 'text',
                'helper' => 'Enter the URL where the button should link to.',
                'supplemental' => '',
            ),

            // Open link in new tab
            array(
                'uid' => 'floating_cta_button_link_new_tab',
                'label' => 'Open Link in New Tab',
                'section' => 'floating_cta_button_section',
                'type' => 'checkbox',
                'helper' => 'Check this to open the button link in a new tab.',
                'supplemental' => '',
            ),

            // Button styling options
            array(
                'uid' => 'floating_cta_button_color',
                'label' => 'Button Color',
                'section' => 'floating_cta_button_section',
                'type' => 'color',
                'helper' => 'Choose the button color.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_style',
                'label' => 'Button Style',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    'rounded' => 'Rounded',
                    'round' => 'Round',
                    'square' => 'Square',
                ),
                'helper' => 'Choose button style.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_padding',
                'label' => 'Button Padding',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    'extra-small' => 'Extra Small',
                    'small' => 'Small',
                    'medium' => 'Medium',
                    'large' => 'Large',
                    'extra-large' => 'Extra Large',
                ),
                'helper' => 'Choose button padding.',
                'supplemental' => '',
            ),
            
            // Button Icon
            array(
                'uid' => 'floating_cta_button_icon',
                'label' => 'Button Icon',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => $this->get_font_awesome_icons(),
                'helper' => 'Choose an icon from Font Awesome.',
                'supplemental' => '',
            ),

            // Button Icon spacing
            array(
                'uid' => 'floating_cta_button_icon_spacing',
                'label' => 'Button Icon Spacing (px)',
                'section' => 'floating_cta_button_section',
                'type' => 'number',
                'helper' => 'Enter Icon & Text Spacing',
                'supplemental' => '',
            ),

            // Button Icon size
            array(  
                'uid' => 'floating_cta_button_icon_size',
                'label' => 'Button Icon Size (px)',
                'section' => 'floating_cta_button_section',
                'type' => 'number',
                'helper' => 'Enter Icon Size',
                'supplemental' => '',
            ),

            // Button position
            array(
                'uid' => 'floating_cta_button_position',
                'label' => 'Button Position',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                ),
                'helper' => 'Choose button position.',
                'supplemental' => '',
            ),

            // Gradient options (premium feature)
            array(
                'uid' => 'floating_cta_button_gradient_enabled',
                'label' => 'Enable Gradient Background',
                'section' => 'floating_cta_button_section',
                'type' => 'checkbox',
                'default' => false,
                'helper' => 'Enable a gradient background for the button.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_gradient_start_color',
                'label' => 'Gradient Start Color',
                'section' => 'floating_cta_button_section',
                'type' => 'color',
                'default' => '#ff0000',
                'helper' => 'Choose the starting color for the gradient.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_gradient_end_color',
                'label' => 'Gradient End Color',
                'section' => 'floating_cta_button_section',
                'type' => 'color',
                'default' => '#0000ff',
                'helper' => 'Choose the ending color for the gradient.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_gradient_direction',
                'label' => 'Gradient Direction',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    'to right' => 'Left to Right',
                    'to left' => 'Right to Left',
                    'to bottom' => 'Top to Bottom',
                    'to top' => 'Bottom to Top',
                    'to bottom right' => 'Top Left to Bottom Right',
                    'to top right' => 'Bottom Left to Top Right',
                    'to bottom left' => 'Top Right to Bottom Left',
                    'to top left' => 'Bottom Right to Top Left',
                ),
                'default' => 'to right',
                'helper' => 'Choose the direction of the gradient.',
                'supplemental' => '',
            ),

            // Drop shadow options
            array(
                'uid' => 'floating_cta_button_dropshadow_enabled',
                'label' => 'Enable Drop Shadow',
                'section' => 'floating_cta_button_section',
                'type' => 'checkbox',
                'helper' => 'Check to enable drop shadow for the button.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_dropshadow_color',
                'label' => 'Drop Shadow Color',
                'section' => 'floating_cta_button_section',
                'type' => 'color',
                'helper' => 'Choose the drop shadow color.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_dropshadow_position',
                'label' => 'Drop Shadow Position',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    'center' => 'Center',
                    'left' => 'Left',
                    'right' => 'Right',
                ),
                'helper' => 'Choose drop shadow position.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_dropshadow_size',
                'label' => 'Drop Shadow Size (px)',
                'section' => 'floating_cta_button_section',
                'type' => 'number',
                'helper' => 'Set the drop shadow size in pixels.',
                'supplemental' => '',
            ),

            // Font settings
            array(
                'uid' => 'floating_cta_button_font_family',
                'label' => 'Font Family',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => $this->get_google_fonts(),
                'helper' => 'Select font family.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_font_weight',
                'label' => 'Font Weight',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    'normal' => 'Normal',
                    'bold' => 'Bold',
                ),
                'helper' => 'Select font weight.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_font_size',
                'label' => 'Font Size (px)',
                'section' => 'floating_cta_button_section',
                'type' => 'number',
                'helper' => 'Set the font size in pixels.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_font_color',
                'label' => 'Font Color',
                'section' => 'floating_cta_button_section',
                'type' => 'color',
                'helper' => 'Choose the font color.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_letter_spacing',
                'label' => 'Letter Spacing',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    'inherit' => 'Inherit',
                    '-1' => '-1px',
                    '0' => '0px',
                    '1' => '1px',
                    '2' => '2px',
                    '3' => '3px',
                    '4' => '4px',
                    '5' => '5px',
                ),
                'default' => 'inherit',
                'helper' => 'Adjust the letter spacing for the button text.',
                'supplemental' => '',
            ),

            // Transparency options
            array(
                'uid' => 'floating_cta_button_transparent_bg_enabled',
                'label' => 'Transparent Background',
                'section' => 'floating_cta_button_section',
                'type' => 'checkbox',
                'helper' => 'Enable transparent background for the button.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_transparent_bg_opacity',
                'label' => 'Background Transparency Level',
                'section' => 'floating_cta_button_section',
                'type' => 'range',
                'min' => 0,
                'max' => 1,
                'step' => 0.01,
                'helper' => 'Set the transparency level for the background.',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_animation',
                'label' => 'Button Animation',
                'section' => 'floating_cta_button_section',
                'type' => 'select',
                'options' => array(
                    '' => 'None',
                    'fade-in-animation' => 'Fade',
                    'scale-up-animation' => 'Scale',
                    'rotate-animation' => 'Rotate',
                    'bounce-animation' => 'Bounce',
                    'slide-in-left-animation' => 'Slide In Left',
                    'slide-in-right-animation' => 'Slide In Right',
                    'pulse-animation' => 'Pulse',
                ),
                'helper' => 'Choose the animation to apply to the button',
                'supplemental' => '',
            ),
            array(
                'uid' => 'floating_cta_button_background_bar',
                'label' => 'Button Background Bar',
                'section' => 'floating_cta_button_section',
                'type' => 'checkbox',
                'disabled' => true,
                'helper' => 'Enable Button Background Bar',
                'supplemental' => '',
            ),
        );

        // Loop through the fields and add them to the settings page
        foreach ($fields as $field) {
            add_settings_field(
                $field['uid'], // Field ID
                $field['label'], // Field label
                array($this, 'field_callback'), // Callback function to render the field
                'floating_cta_button', // Page slug
                $field['section'], // Section ID
                $field // Arguments
            );
            // Register each setting
            register_setting('floating_cta_button', $field['uid']);
        }
    }

    // Callback function to render each field
    public function field_callback($arguments) {
        // Get the current value of the field from the database
        $value = get_option($arguments['uid']);
        switch ($arguments['type']) {
            case 'text': // Text input
                printf('<input name="%1$s" id="%1$s" type="text" value="%2$s" />', $arguments['uid'], $value);
                break;
            case 'number': // Number input
                printf('<input name="%1$s" id="%1$s" type="number" value="%2$s" />', $arguments['uid'], $value);
                break;
            case 'textarea': // Textarea
                printf('<textarea name="%1$s" id="%1$s">%2$s</textarea>', $arguments['uid'], $value);
                break;
            case 'select': // Select dropdown
                if (!empty($arguments['multiple'])) {
                    printf('<select name="%1$s[]" id="%1$s" multiple>', $arguments['uid']);
                } else {
                    printf('<select name="%1$s" id="%1$s">', $arguments['uid']);
                }
                foreach ($arguments['options'] as $key => $label) {
                    $selected = '';
                    if (is_array($value)) {
                        if (in_array($key, $value)) {
                            $selected = 'selected="selected"';
                        }
                    } else {
                        if ($value == $key) {
                            $selected = 'selected="selected"';
                        }
                    }
                    printf('<option value="%s" %s>%s</option>', $key, $selected, $label);
                }
                printf('</select>');
                break;
            case 'range':
                printf(
                    '<input name="%1$s" id="%1$s" type="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" />',
                    esc_attr($arguments['uid']),
                    esc_attr($arguments['type']),
                    esc_attr($value),
                    isset($arguments['min']) ? esc_attr($arguments['min']) : '',
                    isset($arguments['max']) ? esc_attr($arguments['max']) : '',
                    isset($arguments['step']) ? esc_attr($arguments['step']) : ''
                );
                break;
            case 'checkbox': // Checkbox input
                printf('<input name="%1$s" id="%1$s" type="checkbox" %2$s />', $arguments['uid'], $value ? 'checked="checked"' : '');
                break;
            case 'color': // Color picker
                printf('<input name="%1$s" id="%1$s" type="color" value="%2$s" />', $arguments['uid'], $value);
                break;
                
        }
        // Display helper text, if any
        if ($helper = $arguments['helper']) {
            printf('<span class="helper"> %s</span>', $helper);
        }
        // Display supplemental text, if any
        if ($supplemental = $arguments['supplemental']) {
            printf('<p class="description">%s</p>', $supplemental);
        }
    }

    // Function to get a list of all pages on the site
    public function get_pages() {
        $pages = get_pages();
        $options = array();
        if ($pages) {
            foreach ($pages as $page) {
                $options[$page->ID] = $page->post_title;
            }
        }
        return $options;
    }

    public function add_google_font() { 
        wp_enqueue_style( 'roboto', 'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap', false );
        wp_enqueue_style( 'open-sans', 'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap', false );
        wp_enqueue_style( 'poppins', 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap', false );
        wp_enqueue_style( 'lato', 'https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap', false );
        wp_enqueue_style( 'montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap', false );
        wp_enqueue_style( 'source-sans-pro', 'https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&display=swap', false );
        wp_enqueue_style( 'merriweather', 'https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap', false );
        wp_enqueue_style( 'raleway', 'https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap', false );
        wp_enqueue_style( 'pt-sans', 'https://fonts.googleapis.com/css2?family=PT+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap', false );
        wp_enqueue_style( 'noto-sans', 'https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap', false );
        wp_enqueue_style( 'josefin-sans', 'https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap', false );
        wp_enqueue_style( 'nunito', 'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap', false );
        wp_enqueue_style( 'oswald', 'https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap', false );
        wp_enqueue_style( 'arsenal', 'https://fonts.googleapis.com/css2?family=Arsenal:ital,wght@0,400;0,700;1,400;1,700&display=swap', false );
        wp_enqueue_style( 'sofadi-one', 'https://fonts.googleapis.com/css2?family=Sofadi+One&display=swap', false );
        wp_enqueue_style( 'protest-guerrilla', 'https://fonts.googleapis.com/css2?family=Protest+Guerrilla&display=swap', false );
        wp_enqueue_style( 'inter', 'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap', false );
        wp_enqueue_style( 'roboto-mono', 'https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap', false );
        wp_enqueue_style( 'ubuntu', 'https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap', false );
        wp_enqueue_style( 'prompt', 'https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap', false );
        wp_enqueue_style( 'sofadi-one', 'https://fonts.googleapis.com/css2?family=Sofadi+One&display=swap', false );
        wp_enqueue_style( 'playfair-display', 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap', false );
        wp_enqueue_style( 'quicksand', 'https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap', false );
        wp_enqueue_style( 'roboto-condensed', 'https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap', false );
        wp_enqueue_style( 'source-sans-3', 'https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap', false );
        wp_enqueue_style( 'source-sans-3', 'https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap', false );
    }

    public function get_google_fonts() {

        return array(
            'Open Sans' => 'Open Sans',
            'Roboto' => 'Roboto',
            'Poppins' => 'Poppins',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
            'Source Sans Pro' => 'Source Sans Pro',
            'Merriweather' => 'Merriweather',
            'Raleway' => 'Raleway',
            'PT Sans' => 'PT Sans',
            'Noto Sans' => 'Noto Sans',
            'Josefin Sans' => 'Josefin Sans',
            'Nunito' => 'Nunito',
            'Oswald' => 'Oswald',
            'Arsenal' => 'Arsenal',
            'Sofadi One' => 'Sofadi One',
            'Protest Guerrilla' => 'Protest Guerrilla',
            'inter' => 'inter',
            'Roboto Mono' => 'Roboto Mono',
            'Ubuntu' => 'Ubuntu',
            'Prompt' => 'Prompt',
            'Sofadi One' => 'Sofadi One',
            'Playfair Display' => 'Playfair Display',
            'Quicksand' => 'Quicksand',
            'Roboto Condensed' => 'Roboto Condensed',
            'Source Sans 3' => 'Source Sans 3',
            'Source Sans 3' => 'Source Sans 3',
        );
    
    }
    
    // Function to get a list of Font Awesome icons
    public function get_font_awesome_icons() {
        return array(
            'None' => 'None',
            'fa-envelope' => 'Envelope',
            'fa-phone' => 'Phone',
            'fa-comment' => 'Comment',
            'fa-info-circle' => 'Info Circle',
            'fa-exclamation-triangle' => 'Exclamation Triangle',
            'fa-check-circle' => 'Check Circle',
            'fa-home' => 'Home',
            'fa-user' => 'User',
            'fa-lock' => 'Lock',
            'fa-unlock' => 'Unlock',
            'fa-calendar' => 'Calendar',
            'fa-bell' => 'Bell',
            'fa-shopping-cart' => 'Shopping Cart',
            'fa-gift' => 'Gift',
            'fa-heart' => 'Heart',
            'fa-star' => 'Star',
            'fa-trash' => 'Trash',
            'fa-edit' => 'Edit',
            'fa-search' => 'Search',
            'fa-cog' => 'Settings',
            'fa-upload' => 'Upload',
            'fa-download' => 'Download',
            'fa-camera' => 'Camera',
            'fa-globe' => 'Globe',
            'fa-map-marker' => 'Map Marker',
            'fa-paper-plane' => 'Paper Plane',
            'fa-key' => 'Key',
            'fa-wrench' => 'Wrench',
            'fa-music' => 'Music',
            'fa-video' => 'Video',
            'fa-comments' => 'Comments',
            'fa-credit-card' => 'Credit Card',
            'fa-print' => 'Print',
            'fa-folder' => 'Folder',
            'fa-thumbs-up' => 'Thumbs Up',
            'fa-thumbs-down' => 'Thumbs Down',
            'fa-shield-alt' => 'Shield',
            'fa-smile' => 'Smile',
            'fa-meh' => 'Meh',
            'fa-frown' => 'Frown',
            
            // Social Media Icons
            'fa-facebook' => 'Facebook',
            'fa-twitter' => 'Twitter',
            'fa-linkedin' => 'LinkedIn',
            'fa-instagram' => 'Instagram',
            'fa-pinterest' => 'Pinterest',
            'fa-youtube' => 'YouTube',
            'fa-reddit' => 'Reddit',
            'fa-tiktok' => 'TikTok',
            'fa-whatsapp' => 'WhatsApp',
            'fa-telegram' => 'Telegram',
            'fa-snapchat' => 'Snapchat',
            'fa-vimeo' => 'Vimeo',
            'fa-tumblr' => 'Tumblr',
            'fa-github' => 'GitHub',
            'fa-behance' => 'Behance',
            'fa-dribbble' => 'Dribbble',
            'fa-medium' => 'Medium',
            'fa-skype' => 'Skype'
        );
        
    }

    public function enqueue_scripts() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery');
        wp_enqueue_script('floating-cta-button-script', plugins_url('/js/script.js', __FILE__), array('jquery'), '', true);
        wp_enqueue_style('floating-cta-button-style', plugins_url('/css/style.css', __FILE__));

        // Enqueue Font Awesome for icons
        wp_enqueue_style('floating-cta-button-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
        wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css' );
    }

    // Function to display the CTA button on the front-end
    public function display_button() {
        // Check if the CTA button is enabled
        $is_enabled = get_option('floating_cta_button_enabled', false);
        if (!$is_enabled) {
            return; // Do not display the button if it's disabled
        }

        // Get the selected pages from the options
        $pages = get_option('floating_cta_button_pages', array());
        // Check if the current page is one of the selected pages
        if (!is_page($pages)) {
            return; // Do not display the button if not on the selected pages
        }

        // Get all the options for the button
        $button_color = get_option('floating_cta_button_color', '#000000');
        $font_family = get_option('floating_cta_button_font_family', 'Arial');
        $font_weight = get_option('floating_cta_button_font_weight', 'normal');
        $font_size = get_option('floating_cta_button_font_size', '16');
        $font_color = get_option('floating_cta_button_font_color', '#ffffff');
        $letter_spacing = get_option('floating_cta_button_letter_spacing', 'inherit');
        $button_style = get_option('floating_cta_button_style', 'rounded');
        $button_padding = get_option('floating_cta_button_padding', 'regular');
        $dropshadow_enabled = get_option('floating_cta_button_dropshadow_enabled', false);
        $dropshadow_color = get_option('floating_cta_button_dropshadow_color', '#000000');
        $dropshadow_position = get_option('floating_cta_button_dropshadow_position', 'right');
        $dropshadow_size = get_option('floating_cta_button_dropshadow_size', '4');
        $transparent_bg_enabled = get_option('floating_cta_button_transparent_bg_enabled', false);
        $transparent_bg_opacity = get_option('floating_cta_button_transparent_bg_opacity', '0.5');
        $button_text = get_option('floating_cta_button_text', 'Contact Now');
        $button_icon = get_option('floating_cta_button_icon', 'fa-envelope');
        $button_icon_spacing = get_option('floating_cta_button_icon_spacing', '5');
        $button_icon_size = get_option('floating_cta_button_icon_size', '16');
        $position = get_option('floating_cta_button_position', 'right');
        $button_link = get_option('floating_cta_button_link', '#');
        $open_in_new_tab = get_option('floating_cta_button_link_new_tab');
        $button_animation = get_option('floating_cta_button_animation');

        // Prepare target attribute
        $target = ($open_in_new_tab) ? ' target="_blank"' : '';

        // Build the inline CSS styles for the button
        $style = '';

        // Apply background if gradient is enabled
        $gradient_enabled = get_option('floating_cta_button_gradient_enabled', false);
        $gradient_start_color = get_option('floating_cta_button_gradient_start_color', '#ff0000');
        $gradient_end_color = get_option('floating_cta_button_gradient_end_color', '#0000ff');
        $gradient_direction = get_option('floating_cta_button_gradient_direction', 'to right');

        if ($gradient_enabled) {
            if ($transparent_bg_enabled) {
                // Use gradient with transparency
                $style .= 'background: linear-gradient(' . esc_attr($gradient_direction) . ', rgba(' . $this->hexToRgb($gradient_start_color) . ',' . $transparent_bg_opacity . '), rgba(' . $this->hexToRgb($gradient_end_color) . ',' . $transparent_bg_opacity . '));';
            } else {
                // Use gradient without transparency
                $style .= 'background: linear-gradient(' . esc_attr($gradient_direction) . ', ' . esc_attr($gradient_start_color) . ', ' . esc_attr($gradient_end_color) . ');';
            }
        } else {
            // Use solid color if no gradient
            $style .= $transparent_bg_enabled
                ? 'background-color: rgba(' . $this->hexToRgb($button_color) . ',' . $transparent_bg_opacity . ');'
                : 'background-color: ' . $button_color . ';';
        }

        $style .= 'font-family: ' . $font_family . ';';
        $style .= 'font-weight: ' . $font_weight . ';';
        $style .= $font_size ? 'font-size: ' . $font_size . 'px;' : 'font-size: 16px;';
        $style .= 'color: ' . $font_color . ';';

        if ($letter_spacing == 'inherit') {
            $style .= 'letter-spacing: ' . $letter_spacing . ';';
        } else {
            $style .= 'letter-spacing: ' . $letter_spacing . 'px;';
        }

        if ($dropshadow_enabled) {
            switch ($dropshadow_position) {
                case 'left':
                    $style .= 'box-shadow: -' . $dropshadow_size . 'px 0 8px 0 ' . $dropshadow_color . ';';
                    break;
                case 'right':
                    $style .= 'box-shadow: ' . $dropshadow_size . 'px 0 8px 0 ' . $dropshadow_color . ';';
                    break;
                case 'center':
                    $style .= 'box-shadow: 0 0 ' . $dropshadow_size . 'px ' . $dropshadow_color . ';';
                    break;
            }
        }

        // Button Icon & Text Spacing
        $button_icon_text_spacing = $button_icon_spacing ? 'padding-right: ' . $button_icon_spacing . 'px;' : 'padding-right: 5px;';
        $button_icon_size_style = $button_icon_size ? 'font-size: ' . $button_icon_size . 'px;' : 'font-size: 16px;';


        // Button Styles
        if ($button_style == 'rounded') {
            $style .= 'border-radius: 5px;';
        } elseif ($button_style == 'round') {
            $style .= 'border-radius: 25px;';
        } else {
            $style .= 'border-radius: 0px;';
        }

        // Button Padding
        if ($button_padding == 'extra-small') {
            $style .= 'padding: 3px 6px;';
        } elseif ($button_padding == 'small') {
            $style .= 'padding: 5px 10px;';
        } elseif ($button_padding == 'medium') {
            $style .= 'padding: 10px 20px;';
        } elseif ($button_padding == 'large') {
            $style .= 'padding: 20px 40px;';
        } elseif ($button_padding == 'extra-large') {
            $style .= 'padding: 30px 60px;';
        }


        // Determine the position class based on the selected option
        $position_class = '';
        switch($position) {
            case 'left':
                $position_class = 'cta-button-left';
                break;
            case 'center':
                $position_class = 'cta-button-center';
                break;
            case 'right':
                $position_class = 'cta-button-right';
                break;
        }

        // Output the button HTML with the generated styles
        echo '<a href="' . esc_url($button_link) . '"' . $target . ' class="floating-cta-link">';
        echo '<div class="floating-cta-button ' . $button_animation . ' ' . $position_class . '" style="' . $style . '">';
        if($button_icon) {
            echo '<i class="fa ' . $button_icon . '" style="' . $button_icon_text_spacing . ';' . $button_icon_size_style . '"></i> ';
        }
        echo $button_text;
        echo '</div>';
        echo '</a>';
    }

    // Helper function to convert hex color to RGB
    private function hexToRgb($hex) {
        $hex = str_replace("#", "", $hex);
        
        if (strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return "$r, $g, $b";
    }

}

new FloatingCTAButton();
