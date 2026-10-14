@props(['themeColor' => '#0066FF'])

{{-- Métadonnées PWA : rendent l'application installable sur smartphone --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="{{ $themeColor }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="IVOSPHERE">
<meta name="application-name" content="IVOSPHERE">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
