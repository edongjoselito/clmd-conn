<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['learning_area_dashboards'] = array(
    'filipino' => array('name' => 'Filipino', 'aliases' => array('Filipino')),
    'english' => array('name' => 'English', 'aliases' => array('English')),
    'mathematics' => array('name' => 'Mathematics', 'aliases' => array('Mathematics', 'Math')),
    'science' => array('name' => 'Science', 'aliases' => array('Science')),
    'ap' => array('name' => 'Araling Panlipunan (AP)', 'aliases' => array('Araling Panlipunan', 'Araling Panlipunan (AP)', 'AP')),
    'gmrc-ve' => array('name' => 'Good Manners and Right Conduct (GMRC) / Values Education (VE)', 'aliases' => array('GMRC', 'VE', 'GMRC / VE', 'Values Education', 'Good Manners and Right Conduct', 'Good Manners and Right Conduct (GMRC) / Values Education (VE)', 'Edukasyon sa Pagpapakatao', 'EsP')),
    'music-arts' => array('name' => 'Music and Arts', 'aliases' => array('Music and Arts', 'Music', 'Arts')),
    'pe-health' => array('name' => 'Physical Education and Health', 'aliases' => array('Physical Education and Health', 'Physical Education', 'Health', 'PE', 'PE and Health')),
    'tle' => array('name' => 'Technology and Livelihood Education (TLE)', 'aliases' => array('Technology and Livelihood Education (TLE)', 'Technology and Livelihood Education', 'TLE', 'EPP', 'Edukasyong Pantahanan at Pangkabuhayan')),
);

// Each learning area owns its visual identity; edit these independently.
$learning_area_themes = array(
    'filipino' => array('primary' => '#8f2345', 'dark' => '#43172c', 'soft' => '#fff1f4', 'tagline' => 'Wika, panitikan, at pagkakakilanlan', 'logo' => 'assets/images/learning-areas/filipino.svg'),
    'english' => array('primary' => '#2458a0', 'dark' => '#142e59', 'soft' => '#eef5ff', 'tagline' => 'Language, literacy, and expression', 'logo' => 'assets/images/learning-areas/english.svg'),
    'mathematics' => array('primary' => '#5145a6', 'dark' => '#252054', 'soft' => '#f2f0ff', 'tagline' => 'Patterns, reasoning, and problem solving', 'logo' => 'assets/images/learning-areas/mathematics.svg'),
    'science' => array('primary' => '#147969', 'dark' => '#103f3c', 'soft' => '#eaf9f3', 'tagline' => 'Inquiry, discovery, and evidence', 'logo' => 'assets/images/learning-areas/science.svg'),
    'ap' => array('primary' => '#9a521c', 'dark' => '#4b2c1c', 'soft' => '#fff6e9', 'tagline' => 'History, culture, and civic understanding', 'logo' => 'assets/images/learning-areas/ap.svg'),
    'gmrc-ve' => array('primary' => '#9b3976', 'dark' => '#49203e', 'soft' => '#fff0f8', 'tagline' => 'Character, empathy, and responsible choices', 'logo' => 'assets/images/learning-areas/gmrc-ve.svg'),
    'music-arts' => array('primary' => '#793da3', 'dark' => '#382052', 'soft' => '#faf0ff', 'tagline' => 'Creativity, rhythm, and artistic expression', 'logo' => 'assets/images/learning-areas/music-arts.svg'),
    'pe-health' => array('primary' => '#126e88', 'dark' => '#123b4c', 'soft' => '#ebf9fc', 'tagline' => 'Movement, wellbeing, and healthy habits', 'logo' => 'assets/images/learning-areas/pe-health.svg'),
    'tle' => array('primary' => '#9b4b2e', 'dark' => '#482c24', 'soft' => '#fff3ed', 'tagline' => 'Practical skills, enterprise, and innovation', 'logo' => 'assets/images/learning-areas/tle.svg'),
 );
foreach ($learning_area_themes as $slug => $theme) {
    $config['learning_area_dashboards'][$slug]['theme'] = $theme;
}
unset($learning_area_themes, $slug, $theme);
