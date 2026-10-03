<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    private const STATUSES = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'preparing' => 'Disiapkan',
        'ready' => 'Siap diambil',
        'assigned' => 'Driver ditugaskan',
        'picked_up' => 'Sudah diambil',
        'delivering' => 'Dalam pengantaran',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    private const STATUS_ALIASES = [
        'pending' => ['pending', 'menunggu'],
        'confirmed' => ['confirmed'],
        'preparing' => ['preparing', 'diproses'],
        'ready' => ['ready'],
        'assigned' => ['assigned'],
        'picked_up' => ['picked_up'],
        'delivering' => ['delivering', 'diantar'],
        'completed' => ['completed', 'selesai'],
        'cancelled' => ['cancelled', 'canceled', 'dibatalkan'],
    ];

    public function __invoke(Request $request): View
    {
        $today = now()->startOfDay();
        $weeklyRevenue = [];

        for ($offset = 6; $offset >= 0; $offset--) {
            $day = $today->copy()->subDays($offset);
            $weeklyRevenue[] = [
                'label' => $this->dayLabel($day),
                'amount' => Order::query()
                    ->whereDate('created_at', $day->toDateString())
                    ->whereNotIn('status', self::STATUS_ALIASES['cancelled'])
                    ->sum('total_amount'),
            ];
        }

        $maxRevenue = max(1, ...array_column($weeklyRevenue, 'amount'));
        $chartMax = max(1_000_000, (int) (ceil($maxRevenue / 1_000_000) * 1_000_000));
        $points = collect($weeklyRevenue)
            ->values()
            ->map(fn (array $day, int $index): string => sprintf(
                '%d,%d',
                (int) round($index * (700 / (count($weeklyRevenue) - 1))),
                (int) round(170 - ($day['amount'] / $chartMax * 150)),
            ));

        $chartPath = 'M'.$points->implode(' L');
        $chartFill = $chartPath.' L700,190 L0,190 Z';
        $statuses = collect(self::STATUSES)->map(fn (string $label, string $key): array => [
            'key' => $key,
            'label' => $label,
            'count' => Order::query()->whereDate('created_at', $today)->whereIn('status', self::STATUS_ALIASES[$key])->count(),
        ]);
        $todayOrders = Order::query()->whereDate('created_at', $today);
        $todayOrderCount = (clone $todayOrders)->count();
        $todayRevenue = (clone $todayOrders)->whereNotIn('status', self::STATUS_ALIASES['cancelled'])->sum('total_amount');
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $orders = $this->filteredOrders($search, $status)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', [
            'admin' => $request->user(),
            'stats' => [
                ['label' => 'Pesanan hari ini', 'value' => number_format($todayOrderCount, 0, ',', '.'), 'detail' => 'Pesanan masuk hari ini', 'tone' => 'blue', 'icon' => 'orders'],
                ['label' => 'Pendapatan hari ini', 'value' => 'Rp '.number_format((int) $todayRevenue, 0, ',', '.'), 'detail' => 'Tidak termasuk pesanan batal', 'tone' => 'green', 'icon' => 'revenue'],
                ['label' => 'Pesanan aktif', 'value' => number_format(Order::query()->whereIn('status', ['pending', 'menunggu', 'confirmed', 'preparing', 'diproses', 'ready', 'assigned', 'picked_up', 'delivering', 'diantar'])->count(), 0, ',', '.'), 'detail' => 'Menunggu hingga dalam pengantaran', 'tone' => 'orange', 'icon' => 'active'],
                ['label' => 'Pesanan selesai', 'value' => number_format(Order::query()->whereDate('created_at', $today)->whereIn('status', self::STATUS_ALIASES['completed'])->count(), 0, ',', '.'), 'detail' => 'Pesanan selesai hari ini', 'tone' => 'purple', 'icon' => 'finished'],
            ],
            'deliveries' => $orders,
            'statuses' => $statuses,
            'statusOptions' => self::STATUSES,
            'search' => $search,
            'selectedStatus' => $status,
            'weeklyRevenue' => $weeklyRevenue,
            'weeklyRevenueTotal' => array_sum(array_column($weeklyRevenue, 'amount')),
            'chartPath' => $chartPath,
            'chartFill' => $chartFill,
            'chartMax' => $chartMax,
            'todayOrderCount' => $todayOrderCount,
        ]);
    }

    private function dayLabel(Carbon $day): string
    {
        return $day->translatedFormat('D');
    }

    private function safeCsvValue(string $value): string
    {
        $value = str_replace(["\r\n", "\n", "\r"], ' ', $value);

        return preg_match('/^[\t ]*[=+\-@]/', $value) === 1
            ? "'".$value
            : $value;
    }

    public function create(): View
    {
        return view('admin.orders.create', [
            'statusOptions' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'restaurant_name' => ['required', 'string', 'max:150'],
            'total_amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        DB::transaction(function () use ($data): void {
            $order = Order::query()->create([
                ...$data,
                'order_number' => 'T-'.Str::uuid(),
            ]);
            $order->update([
                'order_number' => sprintf('DY-%s-%06d', now()->format('ymd'), $order->id),
            ]);
        });

        return redirect()->route('admin.dashboard')->with('success', 'Pesanan berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        if ($order->status !== $data['status']) {
            $order->update($data);
            $order->statusHistory()->create([
                'user_id' => $request->user()->id,
                'status' => $data['status'],
            ]);
            $order->customer?->notify(new OrderStatusUpdated($order->order_number, $data['status'], $order->id, 'Status pesanan diperbarui'));
            if ($order->customer) {
                (new PushNotificationService)->sendToUser($order->customer, [
                    'title' => 'Status pesanan diperbarui',
                    'body' => 'Pesanan '.$order->order_number.' sekarang '.$data['status'].'.',
                    'type' => 'order_status_updated',
                    'data' => ['order_id' => (string) $order->id, 'order_number' => $order->order_number, 'status' => $data['status']],
                ]);
            }
        }

        $filters = ['q' => $request->input('q')];

        if ($request->filled('filter_status')) {
            $filters['status'] = $request->input('filter_status');
        }

        return redirect()->route('admin.dashboard', $filters)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function export(Request $request): StreamedResponse
    {
        $orders = $this->filteredOrders(
            trim((string) $request->query('q', '')),
            (string) $request->query('status', ''),
        )->latest();

        return response()->streamDownload(function () use ($orders): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new RuntimeException('Tidak dapat membuat berkas ekspor pesanan.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID Pesanan', 'Pelanggan', 'Telepon', 'Restoran', 'Total (Rp)', 'Status', 'Waktu'], ',', '"', '\\');

            foreach ($orders->cursor() as $order) {
                fputcsv($output, [
                    $this->safeCsvValue($order->order_number),
                    $this->safeCsvValue($order->customer_name),
                    $this->safeCsvValue($order->customer_phone ?? ''),
                    $this->safeCsvValue($order->restaurant_name),
                    $order->total_amount,
                    self::STATUSES[$this->canonicalStatus($order->status)],
                    $order->created_at->format('Y-m-d H:i:s'),
                ], ',', '"', '\\');
            }

            fclose($output);
        }, 'laporan-pesanan-daylivery-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredOrders(string $search, string $status): Builder
    {
        return Order::query()
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('restaurant_name', 'like', "%{$search}%");
            }))
            ->when(
                in_array($status, array_keys(self::STATUSES), true),
                fn (Builder $query) => $query->whereIn('status', self::STATUS_ALIASES[$status]),
            );
    }

    private function canonicalStatus(string $status): string
    {
        foreach (self::STATUS_ALIASES as $canonical => $aliases) {
            if (in_array($status, $aliases, true)) {
                return $canonical;
            }
        }

        return 'pending';
    }
}
