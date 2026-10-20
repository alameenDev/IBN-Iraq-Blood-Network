@props(['name'=>'grid'])
@php($paths=[
 'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
 'building'=>'<path d="M4 21V5l8-3 8 3v16M2 21h20M9 21v-5h6v5M8 7h1m6 0h1M8 11h1m6 0h1"/>',
 'network'=>'<rect x="8" y="2" width="8" height="6" rx="2"/><rect x="2" y="16" width="7" height="6" rx="2"/><rect x="15" y="16" width="7" height="6" rx="2"/><path d="M12 8v4M5.5 16v-4h13v4"/>',
 'users'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 5"/>',
 'shield'=>'<path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6zM8 12l3 3 5-6"/>',
 'requests'=>'<rect x="5" y="4" width="14" height="18" rx="2"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="M9 11h6M9 15h6M9 19h3"/>',
 'drop'=>'<path d="M12 2C9 7 4 11 4 15a8 8 0 0 0 16 0c0-4-5-8-8-13zM8 15a4 4 0 0 0 4 4"/>',
 'pulse'=>'<path d="M2 12h5l3-9 4 18 3-9h5"/>',
 'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
 'arrow'=>'<path d="M19 12H5m6-6-6 6 6 6"/>',
 'plus'=>'<path d="M12 5v14M5 12h14"/>',
 'search'=>'<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>',
 'logout'=>'<path d="M9 4H4v16h5m5-12 4 4-4 4M8 12h12"/>',
 'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
 'lock'=>'<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4M12 14v3"/>',
])
<svg {{ $attributes->class(['icon']) }} width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['grid'] !!}</svg>
