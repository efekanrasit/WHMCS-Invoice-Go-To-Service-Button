<?php
use WHMCS\Database\Capsule;

add_hook('AdminAreaFooterOutput', 1, function($vars) {
    if ($vars['filename'] !== 'invoices' || !isset($_GET['id'])) {
        return;
    }

    $invoiceId = (int) $_GET['id'];
    if (!$invoiceId) {
        return;
    }

    $items = Capsule::table('tblinvoiceitems')
        ->where('invoiceid', $invoiceId)
        ->get();

    $serviceLinks = [];

    foreach ($items as $item) {
        if ($item->relid) {
            $hosting = Capsule::table('tblhosting')->where('id', $item->relid)->first();
            if ($hosting) {
                $serviceLinks[$item->id] = [
                    'userid' => $hosting->userid,
                    'relid' => $item->relid
                ];
            }
        }
    }

    $json = json_encode($serviceLinks);

    return <<<HTML
<script>
(function () {
    const serviceMap = $json;

    document.querySelectorAll("textarea[name^='description[']").forEach(function(textarea) {
        const match = textarea.name.match(/description\\[(\\d+)\\]/);
        if (!match) return;

        const itemId = match[1];
        if (!serviceMap[itemId]) return;

        const { userid, relid } = serviceMap[itemId];

        const row = textarea.closest("tr");
        if (!row) return;

        const deleteCell = row.querySelector("td:last-child");
        if (!deleteCell || deleteCell.querySelector(".btn-hizmete-git")) return;

        const btn = document.createElement("a");
        btn.href = "clientsservices.php?userid=" + userid + "&id=" + relid;
        btn.className = "btn-hizmete-git";
        btn.title = "Go to Service";
        btn.target = "_blank";

        btn.innerHTML = '<i class="fas fa-link" style="margin-left: 6px; font-size: 16px;" title="Go to Service"></i>';

        deleteCell.appendChild(btn);
    });
})();
</script>
HTML;
});
