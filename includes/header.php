<?php
/**
 * Gemeinsamer Header mit dynamischen Meta-Tags aus Settings
 */
require_once __DIR__ . '/../classes/Settings.php';
$settings = Settings::getInstance();
$pageTitle = $pageTitle ?? $settings->appName();
$pageDescription = $pageDescription ?? $settings->appDescription();
$pageKeywords = $pageKeywords ?? $settings->appKeywords();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($settings->companyName()); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($settings->appDomain()); ?>">
    <?php if ($settings->get('app_og_image')): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($settings->get('app_og_image')); ?>">
    <?php endif; ?>
    
    <!-- Canonical -->
    <?php if ($settings->appDomain()): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($settings->appDomain()); ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?php echo $basePath ?? ''; ?>assets/css/style.css">
    <!-- Fonts werden lokal geladen (DSGVO-konform) -->
</head>
