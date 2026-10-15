<?php
require_once __DIR__ . '/api/bootstrap.php';
require_admin();

$search = $_GET['q'] ?? '';

// Obtenir une liste distincte de clients d'après les expéditions
require_once __DIR__ . '/api/lib/mailer.php';

$query = "
    SELECT 
        s.recipient_name as name, 
        s.recipient_email as email, 
        s.recipient_phone as phone,
        MAX(s.created_at) as last_order,
        COUNT(s.id) as total_shipments,
        SUBSTRING_INDEX(GROUP_CONCAT(NULLIF(s.recipient_lang, '') ORDER BY s.id DESC), ',', 1) as preferred_lang,
        MAX(s.country) as latest_country
    FROM shipments s
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (s.recipient_name LIKE ? OR s.recipient_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " GROUP BY s.recipient_email, s.recipient_name, s.recipient_phone ORDER BY last_order DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$page_title = __('customers.page_title');
require __DIR__ . '/templates/admin_header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-primary"><?= escape_html(__('customers.title')) ?></h1>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row gap-4 justify-between items-center bg-slate-50">
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="text" name="q" value="<?= escape_html($search) ?>" placeholder="<?= __attr('customers.search_placeholder') ?>" class="px-4 py-2 w-64 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition-colors"><?= escape_html(__('customers.search_btn')) ?></button>
            <?php if ($search): ?>
                <a href="/customers" class="px-4 py-2 text-slate-500 hover:text-slate-700 underline flex items-center"><?= escape_html(__('customers.reset_btn')) ?></a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-sm border-b border-slate-100">
                    <th class="p-4 font-semibold"><?= escape_html(__('customers.th_name')) ?></th>
                    <th class="p-4 font-semibold"><?= escape_html(__('customers.th_contact')) ?></th>
                    <th class="p-4 font-semibold"><?= escape_html(__('common.language')) ?></th>
                    <th class="p-4 font-semibold text-center"><?= escape_html(__('customers.th_shipments_count')) ?></th>
                    <th class="p-4 font-semibold"><?= escape_html(__('customers.th_last_activity')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500"><?= escape_html(__('customers.no_customers')) ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): 
                        $cLang = resolve_recipient_language([
                            'recipient_lang' => $c['preferred_lang'] ?? null,
                            'recipient_email' => $c['email'] ?? '',
                            'country' => $c['latest_country'] ?? null
                        ]);
                    ?>
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-bold text-slate-800">
                                <?= escape_html($c['name']) ?>
                            </td>
                            <td class="p-4">
                                <div><a href="mailto:<?= escape_html($c['email']) ?>" class="text-accent hover:underline"><?= escape_html($c['email']) ?></a></div>
                                <?php if ($c['phone']): ?>
                                    <div class="text-xs text-slate-500"><?= escape_html($c['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-800" title="<?= escape_html($cLang['source_label']) ?>">
                                    <?= render_flag_svg($cLang['lang'], 'w-4 h-3') ?>
                                    <span class="font-bold uppercase"><?= strtoupper($cLang['lang']) ?></span>
                                    <span class="text-slate-400">·</span>
                                    <span class="text-slate-500 text-[11px]"><?= escape_html($cLang['source_label']) ?></span>
                                </span>
                            </td>
                            <td class="p-4 text-center font-bold text-slate-700">
                                <?= (int)$c['total_shipments'] ?>
                            </td>
                            <td class="p-4 text-sm text-slate-600">
                                <?= format_date($c['last_order'], 'd/m/Y') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/templates/admin_footer.php'; ?>
