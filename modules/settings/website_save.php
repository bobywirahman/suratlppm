<?php
$pdo = db();

function saveSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}

// Hero
saveSetting('hero_badge', trim($_POST['hero_badge'] ?? ''));
saveSetting('hero_title', trim($_POST['hero_title'] ?? ''));
saveSetting('hero_subtitle', trim($_POST['hero_subtitle'] ?? ''));
saveSetting('hero_image', upload_rel($_POST['hero_image'] ?? ''));
for ($i = 1; $i <= 3; $i++) {
    saveSetting("hero_stat{$i}_num", trim($_POST["hero_stat{$i}_num"] ?? ''));
    saveSetting("hero_stat{$i}_label", trim($_POST["hero_stat{$i}_label"] ?? ''));
}
saveSetting('section_layanan_badge', trim($_POST['section_layanan_badge'] ?? ''));
saveSetting('section_layanan_title', trim($_POST['section_layanan_title'] ?? ''));
saveSetting('section_tentang_badge', trim($_POST['section_tentang_badge'] ?? ''));
saveSetting('section_kontak_badge', trim($_POST['section_kontak_badge'] ?? ''));
saveSetting('section_kontak_title', trim($_POST['section_kontak_title'] ?? ''));

// Services
$services = [];
for ($i = 0; $i < 8; $i++) {
    $icon = trim($_POST['service_icon'][$i] ?? '');
    $title = trim($_POST['service_title'][$i] ?? '');
    $desc = trim($_POST['service_desc'][$i] ?? '');
    if ($title) $services[] = ['icon' => $icon, 'title' => $title, 'desc' => $desc];
}
saveSetting('services', json_encode($services));

// About
saveSetting('about_title', $_POST['about_title'] ?? '');
saveSetting('about_content', $_POST['about_content'] ?? '');
saveSetting('about_image', upload_rel($_POST['about_image'] ?? ''));

$features = [];
for ($i = 0; $i < 4; $i++) {
    $icon = trim($_POST['feature_icon'][$i] ?? 'fa-check-circle');
    $title = trim($_POST['feature_title'][$i] ?? '');
    $desc = trim($_POST['feature_desc'][$i] ?? '');
    if ($title) $features[] = ['icon' => $icon, 'title' => $title, 'desc' => $desc];
}
saveSetting('about_features', json_encode($features));

// Stats
for ($i = 1; $i <= 4; $i++) {
    saveSetting("stat{$i}_num", trim($_POST["stat{$i}_num"] ?? ''));
    saveSetting("stat{$i}_label", trim($_POST["stat{$i}_label"] ?? ''));
}

// Contact
saveSetting('contact_address', $_POST['contact_address'] ?? '');
saveSetting('contact_email', trim($_POST['contact_email'] ?? ''));
saveSetting('contact_phone', trim($_POST['contact_phone'] ?? ''));

// Footer & Social
saveSetting('footer_desc', trim($_POST['footer_desc'] ?? ''));
saveSetting('social_instagram', trim($_POST['social_instagram'] ?? ''));
saveSetting('social_youtube', trim($_POST['social_youtube'] ?? ''));
saveSetting('social_facebook', trim($_POST['social_facebook'] ?? ''));

$tab = isset($_POST['active_tab']) ? preg_replace('/[^a-z0-9\-]/', '', $_POST['active_tab']) : '';
$_SESSION['success'] = "Landing page berhasil diperbarui!";
header("Location: ?page=website" . ($tab ? "&tab=$tab" : ""));
exit;
