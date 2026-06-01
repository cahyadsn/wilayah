<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename    : index.php
purpose     : main application page
create      : 150702
last edit   : 2026-06-01 21:54:34
author  	: cahya dsn
demo site 	: https://wilayah.cahyadsn.com/apps
source code : https://github.com/cahyadsn/wilayah/apps
================================================================================
MIT License
copyright (c) 2015-2025 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
session_start();
$theme=isset($_SESSION['theme'])?$_SESSION['theme']:(isset($_GET['theme'])?$_GET['theme']:'light');
define("_AUTHOR","cahyadsn");
$_SESSION['author']='cahyadsn';
$_SESSION['ver']=sha1(rand());
require_once 'inc/db.php';
$version='3.0';
header('Expires: '.date('r'));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
  <title>Data Wilayah v<?php echo $version;?></title>
  <meta name="description" content="Data wilayah administrasi Indonesia sesuai Kepmendagri No 300.2.2-2430 Tahun 2025" />
  <meta name="author" content="Cahya DSN" />
  <meta name="keywords" content="php, mysql, data, administrasi, wilayah, indonesia, kepmendagri,300.2.2-2430,2025, cahyadsn" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@0.4.13/dist/leaflet.draw.css" />

  <style>
    /* ============================================================
       DESIGN SYSTEM — Data Wilayah v<?php echo $version;?>
       Tokens from: High-Performance Infrastructure Stats DESIGN.md
       ============================================================ */
    :root {
      --primary: #5BC0FF;
      --secondary: #0F172A;
      --accent: #7C9BFF;
      --background: #020617;
      --background-elevated: #081122;
      --surface: #16243D;
      --surface-soft: rgba(148, 163, 184, 0.08);
      --surface-strong: rgba(30, 41, 59, 0.9);
      --text-primary: #F8FAFC;
      --text-secondary: #94A3B8;
      --border: rgba(148, 163, 184, 0.18);
      --border-strong: rgba(91, 192, 255, 0.22);
      --input-bg: rgba(8, 17, 34, 0.82);
      --input-bg-hover: rgba(15, 23, 42, 0.96);
      --panel-bg: rgba(8, 17, 34, 0.9);
      --panel-solid: rgba(8, 17, 34, 0.96);
      --selection-bg: rgba(91, 192, 255, 0.22);
      --shadow-card: 0 20px 45px rgba(2, 6, 23, 0.42);
      --shadow-glow: 0 0 0 1px rgba(91,192,255,0.05), 0 0 42px rgba(91,192,255,0.08);
      --font-display: 'Inter', system-ui, -apple-system, sans-serif;
      --font-mono: 'JetBrains Mono', 'SF Mono', 'Fira Code', monospace;
      --radius-sm: 4px;
      --radius-md: 8px;
      --radius-lg: 12px;
    }

    body.theme-light {
      --primary: #2563EB;
      --secondary: #FFFFFF;
      --accent: #1D4ED8;
      --background: #EEF4FF;
      --background-elevated: #F8FBFF;
      --surface: #D8E5F7;
      --surface-soft: rgba(37, 99, 235, 0.06);
      --surface-strong: rgba(255, 255, 255, 0.96);
      --text-primary: #0F172A;
      --text-secondary: #64748B;
      --border: rgba(148, 163, 184, 0.3);
      --border-strong: rgba(37, 99, 235, 0.18);
      --input-bg: rgba(255, 255, 255, 0.92);
      --input-bg-hover: rgba(255, 255, 255, 1);
      --panel-bg: rgba(255, 255, 255, 0.92);
      --panel-solid: rgba(255, 255, 255, 0.98);
      --selection-bg: rgba(37, 99, 235, 0.16);
      --shadow-card: 0 18px 40px rgba(15, 23, 42, 0.08);
      --shadow-glow: 0 1px 0 rgba(255,255,255,0.8) inset;
    }

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    html {
      font-size: 15px;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    body {
      font-family: var(--font-display);
      background:
        radial-gradient(circle at top left, color-mix(in srgb, var(--primary) 12%, transparent), transparent 34%),
        linear-gradient(180deg, var(--background-elevated), var(--background));
      color: var(--text-primary);
      min-height: 100vh;
    }

    ::selection {
      background: var(--selection-bg);
      color: var(--text-primary);
    }

    /* ============================================================
       HEADER / NAV
       ============================================================ */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      height: 52px;
      background: var(--panel-bg);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
    }

    body.theme-light .navbar {
      background: var(--panel-bg);
      border-bottom: 1px solid var(--border);
    }

    .navbar-brand {
      font-family: var(--font-mono);
      font-size: 13px;
      font-weight: 600;
      color: var(--text-primary);
      letter-spacing: 0.02em;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .navbar-brand::before {
      content: '';
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--primary);
      box-shadow: 0 0 8px rgba(74, 185, 255, 0.5);
    }

    .navbar-version {
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--text-secondary);
      background: var(--surface-soft);
      padding: 2px 8px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
    }

    .navbar-meta {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--text-secondary);
      font-family: var(--font-mono);
      font-size: 11px;
      white-space: nowrap;
    }

    .navbar-copyright {
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .navbar-copyright a {
      color: var(--primary);
      text-decoration: none;
    }

    .navbar-copyright a:hover {
      text-decoration: underline;
    }

    .navbar-separator {
      color: var(--text-muted);
    }

    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .theme-toggle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: var(--surface-soft);
      cursor: pointer;
      transition: all 0.2s ease;
      color: var(--text-primary);
    }

    .theme-toggle:hover {
      border-color: var(--primary);
      transform: translateY(-1px);
      box-shadow: 0 10px 28px color-mix(in srgb, var(--primary) 18%, transparent);
    }

    .theme-toggle:focus-visible {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent);
    }

    .theme-toggle svg {
      width: 18px;
      height: 18px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .theme-icon-sun,
    .theme-icon-moon {
      display: none;
    }

    body.theme-dark .theme-icon-sun {
      display: block;
    }

    body.theme-light .theme-icon-moon {
      display: block;
    }

    /* ============================================================
       MAIN LAYOUT
       ============================================================ */
    .app-wrapper {
      padding-top: 52px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .app-main {
      flex: 1;
      max-width: none;
      width: 100%;
      margin: 0 auto;
      padding: 0;
    }

    .layout-grid {
      position: relative;
      min-height: calc(100vh - 52px);
    }

    .sidebar-panel {
      min-width: 0;
      position: absolute;
      top: 12px;
      left: 12px;
      z-index: 500;
      width: min(380px, calc(100vw - 48px));
      transition: opacity 0.2s ease, transform 0.24s ease, width 0.24s ease;
    }

    .layout-grid.is-sidebar-collapsed .sidebar-panel .card-body,
    .layout-grid.is-sidebar-collapsed .sidebar-panel .card-title small {
      display: none;
    }

    .layout-grid.is-sidebar-collapsed .sidebar-panel .card {
      overflow: visible;
    }

    .layout-grid.is-sidebar-collapsed .sidebar-panel {
      width: 56px;
    }

    .layout-grid.is-sidebar-collapsed .sidebar-panel .card-header {
      padding: 10px;
      justify-content: center;
      border-bottom: 0;
    }

    .layout-grid.is-sidebar-collapsed .sidebar-panel .card-title {
      display: none;
    }

    .map-panel {
      min-width: 0;
      width: 100%;
    }

    .sidebar-panel .card-header {
      padding: 16px 18px 12px;
    }

    .sidebar-header-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      width: 100%;
    }

    .sidebar-toggle {
      display: grid;
      place-items: center;
      width: 34px;
      height: 34px;
      flex: 0 0 34px;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: var(--surface-soft);
      color: var(--text-primary);
      cursor: pointer;
      padding: 0;
      line-height: 0;
      transition: all 0.2s ease;
    }

    .sidebar-toggle:hover {
      border-color: var(--border-strong);
      background: color-mix(in srgb, var(--primary) 12%, transparent);
      transform: translateY(-1px);
    }

    .sidebar-toggle:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 18%, transparent);
    }

    .sidebar-toggle svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2.3;
      stroke-linecap: round;
      stroke-linejoin: round;
      transform: translateX(0.5px);
      transition: transform 0.2s ease;
    }

    .layout-grid.is-sidebar-collapsed .sidebar-toggle svg {
      transform: rotate(180deg) translateX(0.5px);
    }

    .sidebar-panel .card-body {
      padding: 14px 18px 18px;
    }

    .sidebar-panel .card-title {
      font-size: 1.08rem;
      line-height: 1.25;
    }

    .sidebar-panel .card-title small {
      font-size: 0.72rem;
      margin-top: 6px;
    }

    .sidebar-panel .form-row {
      gap: 10px;
      margin-bottom: 10px;
    }

    .sidebar-panel .form-group label {
      margin-bottom: 5px;
    }

    .sidebar-panel .form-select {
      padding-top: 9px;
      padding-bottom: 9px;
      font-size: 12px;
    }

    .sidebar-panel .info-strip {
      gap: 10px;
      padding-top: 10px;
    }

    .sidebar-panel .info-item {
      width: 100%;
      justify-content: space-between;
    }

    .map-panel {
      display: flex;
      flex-direction: column;
    }

    .map-panel {
      position: relative;
    }

    .map-panel #map-canvas {
      flex: 1;
    }


    .map-overlay-bar {
      position: absolute;
      left: 16px;
      bottom: 16px;
      z-index: 420;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 10px;
      border-radius: 999px;
      background: var(--panel-solid);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-card);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }

    .map-overlay-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--text-secondary);
    }

    .map-overlay-label {
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-secondary);
    }

    .map-overlay-value {
      color: var(--text-primary);
      font-weight: 600;
    }

    .leaflet-bottom.leaflet-right .leaflet-control-zoom {
      margin-right: 16px;
      margin-bottom: 64px;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-card);
      overflow: hidden;
      border-radius: 14px;
    }

    .leaflet-control-zoom a {
      background: var(--panel-solid) !important;
      color: var(--text-primary) !important;
      border-bottom: 1px solid var(--border) !important;
      width: 36px !important;
      height: 36px !important;
      line-height: 34px !important;
      font-size: 18px !important;
    }

    .leaflet-control-zoom a:last-child {
      border-bottom: 0 !important;
    }

    .leaflet-control-zoom a:hover {
      background: color-mix(in srgb, var(--primary) 12%, var(--panel-solid)) !important;
    }

    @media (min-width: 900px) {
      .map-panel #map-canvas {
        height: calc(100vh - 52px);
        min-height: calc(100vh - 52px);
        margin-top: 0;
      }
    }

    /* ============================================================
       INFO PANEL
       ============================================================ */
    .card {
      background: var(--surface-strong);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-card), var(--shadow-glow);
    }

    .card-header {
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .card-title {
      font-family: var(--font-display);
      font-size: 1.35rem;
      font-weight: 500;
      letter-spacing: -0.01em;
      color: var(--text-primary);
      line-height: 1.3;
    }

    .card-title small {
      display: block;
      font-size: 0.75rem;
      font-weight: 400;
      color: var(--text-secondary);
      margin-top: 4px;
      letter-spacing: 0;
    }

    .card-body {
      padding: 20px 24px 24px;
    }

    /* ============================================================
       FORM / DROPDOWNS
       ============================================================ */
    .form-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 12px;
    }

    .form-row:last-child {
      margin-bottom: 0;
    }

    .form-group {
      flex: 1;
      min-width: 200px;
    }

    .form-group label {
      display: block;
      font-family: var(--font-mono);
      font-size: 11px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--text-secondary);
      margin-bottom: 6px;
    }

    .form-select {
      width: 100%;
      padding: 10px 12px;
      background: var(--input-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      color: var(--text-primary);
      font-family: var(--font-display);
      font-size: 13px;
      font-weight: 400;
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      padding-right: 32px;
      cursor: pointer;
      transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-select:hover {
      background: var(--input-bg-hover);
      border-color: var(--border-strong);
    }

    .form-select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 18%, transparent);
    }

    .form-select option {
      background: var(--secondary);
      color: var(--text-primary);
    }

    .form-group.is-hidden {
      display: none;
    }

    /* ============================================================
       INFO STRIP (kode, lat, lng)
       ============================================================ */
    .info-strip {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      padding: 12px 0 4px;
    }

    .info-item {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 999px;
      background: var(--surface-soft);
      border: 1px solid var(--border);
    }

    .info-item-label {
      font-family: var(--font-mono);
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--text-secondary);
    }

    .info-item-value {
      font-family: var(--font-mono);
      font-size: 12px;
      font-weight: 500;
      color: var(--text-primary);
    }

    /* ============================================================
       PRELOAD
       ============================================================ */
    #preload {
      display: none;
      align-items: center;
      gap: 8px;
      padding: 8px 0;
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--text-secondary);
    }

    #preload.is-visible {
      display: flex;
    }

    .spinner {
      width: 16px;
      height: 16px;
      border: 2px solid var(--border);
      border-top-color: var(--primary);
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* ============================================================
       MAP
       ============================================================ */
    #map-canvas {
      width: 100%;
      height: 480px;
      border-radius: 18px;
      border: 2px solid var(--border);
      background: #101828;
      overflow: hidden;
      box-shadow: var(--shadow-card), var(--shadow-glow);
    }

    @media (min-width: 900px) {
      #map-canvas { height: 560px; }
    }

    /* Darken Leaflet container for theme */
    .leaflet-container {
      background: var(--secondary) !important;
    }

    body.theme-light .leaflet-container {
      background: #EAF1FB !important;
    }

    body.theme-dark .leaflet-container {
      background: #050816 !important;
    }

    .leaflet-control-zoom,
    .leaflet-control-attribution,
    .leaflet-control-layers,
    .leaflet-bar {
      border: 1px solid var(--border) !important;
      box-shadow: var(--shadow-card) !important;
    }

    .leaflet-control-zoom a {
      background: var(--panel-solid) !important;
      color: var(--text-primary) !important;
      border-bottom-color: var(--border) !important;
    }

    .leaflet-control-zoom a:hover {
      background: var(--surface-strong) !important;
      color: var(--primary) !important;
    }

    .leaflet-control-attribution {
      background: color-mix(in srgb, var(--panel-solid) 86%, transparent) !important;
      color: var(--text-secondary) !important;
      backdrop-filter: blur(10px);
    }

    .leaflet-control-attribution a {
      color: var(--primary) !important;
    }

    .basemap-menu {
      position: relative;
      border: 1px solid var(--border);
      border-radius: 10px;
      background: var(--panel-bg);
      box-shadow: var(--shadow-card);
      overflow: visible;
    }

    .basemap-menu-toggle {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      height: 36px;
      padding: 0 12px;
      border: 0;
      background: transparent;
      color: var(--text-primary);
      font-family: var(--font-mono);
      font-size: 11px;
      cursor: pointer;
    }

    .basemap-menu-toggle svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
      flex-shrink: 0;
    }

    .basemap-menu-toggle:focus-visible {
      outline: none;
      box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--primary) 28%, transparent);
      border-radius: 10px;
    }

    .basemap-menu-panel {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      width: 220px;
      padding: 10px;
      border: 1px solid var(--border);
      border-radius: 14px;
      background: var(--panel-solid);
      box-shadow: var(--shadow-card);
      display: flex;
      flex-direction: column;
      gap: 8px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-6px) scale(0.98);
      pointer-events: none;
      transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
    }

    .basemap-menu.is-open .basemap-menu-panel {
      opacity: 1;
      visibility: visible;
      transform: translateY(0) scale(1);
      pointer-events: auto;
    }

    .basemap-option {
      width: 100%;
      display: flex;
      align-items: center;
      gap: 10px;
      text-align: left;
      border: 1px solid transparent;
      border-radius: 10px;
      background: var(--surface-soft);
      color: var(--text-primary);
      font-family: var(--font-display);
      font-size: 12px;
      padding: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .basemap-option:hover,
    .basemap-option.is-active {
      border-color: var(--border-strong);
      background: color-mix(in srgb, var(--primary) 14%, transparent);
    }

    .basemap-option.is-active {
      box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--primary) 38%, transparent), 0 0 0 1px color-mix(in srgb, var(--primary) 12%, transparent);
    }

    .basemap-check {
      margin-left: auto;
      width: 20px;
      height: 20px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
      color: #fff;
      background: var(--primary);
      box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 15%, transparent);
      opacity: 0;
      transform: scale(0.8);
      transition: opacity 0.18s ease, transform 0.18s ease;
    }

    .basemap-option.is-active .basemap-check {
      opacity: 1;
      transform: scale(1);
    }

    .basemap-thumb {
      position: relative;
      width: 56px;
      height: 42px;
      border-radius: 8px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
    }

    .basemap-thumb span {
      position: absolute;
      inset: 0;
    }

    .basemap-thumb-map {
      background:
        linear-gradient(135deg, rgba(59, 130, 246, 0.28), transparent 55%),
        linear-gradient(180deg, #203B2D 0%, #2B5E3F 48%, #1E293B 48%, #23324A 100%);
    }

    .basemap-thumb-map span {
      background:
        linear-gradient(90deg, transparent 0 18%, rgba(255,255,255,0.2) 18% 22%, transparent 22% 100%),
        linear-gradient(0deg, transparent 0 42%, rgba(255,255,255,0.16) 42% 48%, transparent 48% 100%);
      opacity: 0.9;
    }

    .basemap-thumb-satellite {
      background:
        radial-gradient(circle at 30% 30%, rgba(120, 196, 96, 0.9), transparent 30%),
        radial-gradient(circle at 70% 60%, rgba(38, 70, 83, 0.95), transparent 34%),
        linear-gradient(135deg, #24452D 0%, #446B38 35%, #6B4F36 68%, #2A2E37 100%);
    }

    .basemap-thumb-satellite span {
      background: linear-gradient(135deg, rgba(255,255,255,0.12), transparent 45%, rgba(0,0,0,0.18));
    }

    .basemap-thumb-terrain {
      background:
        linear-gradient(180deg, #2E4A2F 0%, #506C38 38%, #8D6E3B 62%, #526D82 100%);
    }

    .basemap-thumb-terrain span {
      background:
        repeating-linear-gradient(160deg,
          rgba(255,255,255,0.18) 0 2px,
          transparent 2px 7px);
      opacity: 0.75;
    }

    .basemap-option-text {
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 0;
    }

    .basemap-option-text strong {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-primary);
    }

    .basemap-option-text small {
      font-size: 11px;
      color: var(--text-secondary);
      line-height: 1.25;
    }

    .basemap-labels-row {
      display: none;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 8px 2px 2px;
      border-top: 1px solid var(--border);
      cursor: pointer;
    }

    .basemap-labels-row.is-visible {
      display: flex;
    }

    .basemap-labels-copy {
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 0;
    }

    .basemap-labels-copy strong {
      font-size: 12px;
      color: var(--text-primary);
    }

    .basemap-labels-copy small {
      font-size: 11px;
      color: var(--text-secondary);
      line-height: 1.25;
    }

    .basemap-switch {
      position: relative;
      display: inline-flex;
      align-items: center;
      flex-shrink: 0;
    }

    .basemap-switch input {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .basemap-switch-ui {
      width: 38px;
      height: 22px;
      border-radius: 999px;
      background: var(--surface-soft);
      border: 1px solid var(--border);
      position: relative;
      transition: all 0.18s ease;
    }

    .basemap-switch-ui::after {
      content: '';
      position: absolute;
      top: 2px;
      left: 2px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: #fff;
      transition: transform 0.18s ease;
    }

    .basemap-switch input:checked + .basemap-switch-ui {
      background: var(--primary);
      border-color: var(--primary);
    }

    .basemap-switch input:checked + .basemap-switch-ui::after {
      transform: translateX(16px);
    }

    body.theme-light .basemap-menu,
    body.theme-light .basemap-menu-panel {
      background: rgba(255, 255, 255, 0.96);
      border-color: rgba(209, 213, 219, 0.9);
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    body.theme-light .basemap-option {
      background: rgba(217, 226, 242, 0.45);
    }

    body.theme-light .basemap-thumb {
      border-color: rgba(17, 24, 39, 0.08);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.3);
    }

    body.theme-light .basemap-option:hover,
    body.theme-light .basemap-option.is-active {
      background: rgba(37, 99, 235, 0.12);
      border-color: rgba(37, 99, 235, 0.25);
    }

    /* ============================================================
       FOOTER
       ============================================================ */
    .app-footer {
      display: none;
    }

    .app-footer a {
      color: var(--primary);
      text-decoration: none;
    }

    .app-footer a:hover {
      text-decoration: underline;
    }


    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 768px) {
      .app-main { padding: 0; }
      .layout-grid { min-height: auto; }
      .sidebar-panel {
        position: relative;
        top: 0;
        left: 0;
        width: 100%;
        margin-bottom: 10px;
      }
      .layout-grid.is-sidebar-collapsed .sidebar-panel {
        width: 100%;
      }
      .layout-grid.is-sidebar-collapsed .sidebar-panel .card-title,
      .layout-grid.is-sidebar-collapsed .sidebar-panel .card-title small,
      .layout-grid.is-sidebar-collapsed .sidebar-panel .card-body {
        display: block;
      }
      .sidebar-panel .card-header { padding: 14px 16px 10px; }
      .sidebar-panel .card-body { padding: 12px 16px 16px; }
      .card-header { padding: 16px; }
      .card-body { padding: 12px 16px 16px; }
      .form-group { min-width: 100%; }
      #map-canvas { height: calc(100vh - 52px); min-height: calc(100vh - 52px); }
      .navbar { padding: 0 12px; }
      .navbar-meta { gap: 6px; }
      .navbar-copyright { display: none; }
      .theme-toggle {
        width: 32px;
        height: 32px;
      }
      .basemap-menu-toggle {
        height: 32px;
        padding: 0 10px;
        gap: 6px;
        font-size: 10px;
      }
      .basemap-menu-panel {
        width: 196px;
        padding: 8px;
        gap: 6px;
      }
      .basemap-option {
        gap: 8px;
        padding: 7px;
      }
      .basemap-thumb {
        width: 48px;
        height: 36px;
      }
      .basemap-option-text strong,
      .basemap-labels-copy strong {
        font-size: 11px;
      }
      .basemap-option-text small,
      .basemap-labels-copy small {
        font-size: 10px;
      }
      .basemap-check {
        width: 18px;
        height: 18px;
        font-size: 10px;
      }
      .basemap-labels-row {
        gap: 10px;
        padding-top: 6px;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
      }
    }
  </style>
</head>
<body class="theme-<?php echo $theme==='light'?'light':'dark'; ?>">

  <!-- ======== NAVBAR ======== -->
  <nav class="navbar">
    <div class="navbar-brand">Wilayah</div>
    <div class="navbar-meta">
      <span class="navbar-version">v<?php echo $version;?></span>
      <span class="navbar-separator">|</span>
      <span class="navbar-copyright">
        copyright &copy; 2017<?php echo (date('Y')>2017?date('-Y'):'');?>
        by <a href="mailto:cahyadsn@gmail.com">cahya dsn</a>
        <span class="navbar-separator">|</span>
        <a href="https://github.com/cahyadsn/wilayah">source</a>
      </span>
    </div>
    <div class="navbar-actions" id="themeBar">
      <button
        class="theme-toggle"
        id="themeToggle"
        type="button"
        aria-label="Toggle dark/light theme"
        title="Toggle dark/light theme"
      >
        <svg class="theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="4"></circle>
          <path d="M12 2v2"></path>
          <path d="M12 20v2"></path>
          <path d="M4.93 4.93l1.41 1.41"></path>
          <path d="M17.66 17.66l1.41 1.41"></path>
          <path d="M2 12h2"></path>
          <path d="M20 12h2"></path>
          <path d="M4.93 19.07l1.41-1.41"></path>
          <path d="M17.66 6.34l1.41-1.41"></path>
        </svg>
        <svg class="theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 12.79A9 9 0 1 1 11.21 3c0.5 0 0.79 0.57 0.47 0.96A7 7 0 0 0 20.04 12.3c0.39-0.32 0.96-0.03 0.96 0.49z"></path>
        </svg>
      </button>
    </div>
  </nav>

  <!-- ======== MAIN ======== -->
  <div class="app-wrapper">
    <div class="app-main">

      <div class="layout-grid">
        <div class="sidebar-panel">
          <div class="card">
            <div class="card-header">
              <div class="sidebar-header-row">
                <div>
                  <div class="card-title">
                    Data Wilayah Administrasi Indonesia
                    <small>Sesuai Kepmendagri No 300.2.2-2430 Tahun 2025</small>
                  </div>
                </div>
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Tutup panel selector" title="Tutup panel selector">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M14.5 6.5L9 12l5.5 5.5"></path>
                  </svg>
                </button>
              </div>
            </div>

            <div class="card-body">
              <!-- Preload indicator -->
              <div id="preload">
                <div class="spinner"></div>
                <span>Loading...</span>
              </div>

              <!-- Province -->
              <div class="form-row">
                <div class="form-group">
                  <label for="prop">Provinsi</label>
                  <select name="prop" id="prop" class="form-select" onchange="ajax(this.value)">
                    <option value="">Pilih Provinsi</option>
                    <?php
                    $cache_file = sys_get_temp_dir() . '/provinsi_cache.html';
                    $cache_ttl = 86400;

                    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
                      echo file_get_contents($cache_file);
                    } else {
                      $query=$db->prepare("SELECT kode,nama FROM {$tbl_wilayah} WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
                      $query->execute();
                      $html = '';
                      while ($data=$query->fetchObject()){
                        $html .= '<option value="'.$data->kode.'">'.$data->nama.'</option>';
                      }
                      file_put_contents($cache_file, $html, LOCK_EX);
                      echo $html;
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group" id="kab_box" style="display:none;">
                  <label for="kota">Kota / Kabupaten</label>
                  <select name="kota" id="kota" class="form-select" onchange="ajax(this.value)">
                    <option value="">Pilih Kota</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group" id="kec_box" style="display:none;">
                  <label for="kec">Kecamatan</label>
                  <select name="kec" id="kec" class="form-select" onchange="ajax(this.value)">
                    <option value="">Pilih Kecamatan</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group" id="kel_box" style="display:none;">
                  <label for="kel">Desa / Kelurahan</label>
                  <select name="kel" id="kel" class="form-select" onchange="geo(this.value)">
                    <option value="">Pilih Desa</option>
                  </select>
                </div>
              </div>


            </div>
          </div>
        </div>

        <div class="map-panel">
          <div id="map-canvas"></div>
          <div class="map-overlay-bar" id="infoStrip">
            <div class="map-overlay-item"><span class="map-overlay-label">Kode</span><span class="map-overlay-value" id="n_kode">—</span></div>
            <div class="map-overlay-item"><span class="map-overlay-label">Lat</span><span class="map-overlay-value" id="n_lat">—</span></div>
            <div class="map-overlay-item"><span class="map-overlay-label">Lng</span><span class="map-overlay-value" id="n_lng">—</span></div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="app-footer">
        Wilayah v<?php echo $version;?> copyright &copy; 2017<?php echo (date('Y')>2017?date('-Y'):'');?>
        by <a href="mailto:cahyadsn@gmail.com">cahya dsn</a> &mdash;
        source: <a href="https://github.com/cahyadsn/wilayah">github.com/cahyadsn/wilayah</a>
      </div>

    </div>
  </div>

  <!-- ======== SCRIPTS ======== -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://unpkg.com/leaflet-draw@0.4.13/dist/leaflet.draw.js"></script>
  <script src="inc/geo_js.php?v=<?php echo $_SESSION['ver'];?>"></script>
  <script src="js/wilayah.js?id=<?php echo MD5(date('YmdHis'));?>"></script>

</body>
</html>
