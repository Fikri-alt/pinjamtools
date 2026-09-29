@php
$role = Auth::user()->role ?? null;
$isOps = in_array($role, ['admin', 'super_admin'], true);
$isSuper = $role === 'super_admin';
$links = [
    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'show' => true],
    ['route' => 'admin.pengajuan', 'label' => 'Antrean', 'show' => true],
    ['route' => 'admin.loans.aktif', 'label' => 'Aktif', 'show' => true],
    ['route' => 'admin.loans.riwayat', 'label' => 'Riwayat', 'show' => true],
    ['route' => 'admin.loans.walkin', 'label' => 'Walk-in', 'show' => $isOps],
    ['route' => 'admin.items.index', 'label' => 'Inventaris', 'show' => true],
    ['route' => 'admin.damages.index', 'label' => 'Kerusakan', 'show' => true],
    ['route' => 'admin.reports.index', 'label' => 'Laporan', 'show' => true],
    ['route' => 'admin.users.index', 'label' => 'Users', 'show' => $isSuper],
];
@endphp
<div class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2 text-sm">
    @foreach($links as $l)
        @if($l['show'])
        <a href="{{ route($l['route']) }}"
           class="px-3 py-1 rounded {{ request()->routeIs($l['route'].'*') ? 'bg-slate-900 text-white font-semibold' : 'bg-gray-100 hover:bg-gray-200' }}">{{ $l['label'] }}</a>
        @endif
    @endforeach
</div>
