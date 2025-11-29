<?php
/**
 * Adinkra Symbols Component
 * Traditional Ghanaian symbols for decorative use throughout the platform
 * 
 * Usage: include '../includes/adinkra_symbols.php';
 * Then use: echo get_adinkra_symbol('sankofa', '24px');
 */

/**
 * Get Adinkra symbol SVG
 * @param string $symbol_name - Name of the symbol (sankofa, gye_nyame, akoma, etc.)
 * @param string $size - Size of the symbol (e.g., '24px', '48px')
 * @param string $color - Color of the symbol (default: #d4a017 - Ghana gold)
 * @return string - SVG HTML
 */
function get_adinkra_symbol($symbol_name, $size = '24px', $color = '#d4a017') {
    $symbols = [
        'sankofa' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L2 12L12 22M12 2L22 12L12 22" stroke="' . $color . '" stroke-width="2" stroke-linecap="round"/>
            <path d="M12 8V16" stroke="' . $color . '" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="12" r="2" fill="' . $color . '"/>
        </svg>',
        'gye_nyame' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="' . $color . '" stroke-width="2"/>
            <path d="M12 2C8 2 5 5 5 9C5 13 8 16 12 16C16 16 19 13 19 9C19 5 16 2 12 2Z" fill="' . $color . '"/>
            <circle cx="12" cy="9" r="3" fill="white"/>
        </svg>',
        'akoma' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="' . $color . '"/>
        </svg>',
        'nsoromma' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="' . $color . '" stroke-width="2"/>
            <path d="M12 2L14.5 8.5L21 11L14.5 13.5L12 20L9.5 13.5L3 11L9.5 8.5L12 2Z" fill="' . $color . '"/>
        </svg>',
        'denkyem' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="12" cy="12" rx="8" ry="6" stroke="' . $color . '" stroke-width="2" fill="none"/>
            <circle cx="9" cy="10" r="1.5" fill="' . $color . '"/>
            <circle cx="15" cy="10" r="1.5" fill="' . $color . '"/>
            <path d="M12 14Q12 16 10 16" stroke="' . $color . '" stroke-width="2" fill="none" stroke-linecap="round"/>
        </svg>'
    ];
    
    return isset($symbols[$symbol_name]) ? $symbols[$symbol_name] : '';
}

/**
 * Get random Adinkra symbol for decorative purposes
 */
function get_random_adinkra($size = '24px', $color = '#d4a017') {
    $symbols = ['sankofa', 'gye_nyame', 'akoma', 'nsoromma', 'denkyem'];
    $random = $symbols[array_rand($symbols)];
    return get_adinkra_symbol($random, $size, $color);
}
?>

