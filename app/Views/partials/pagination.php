<?php

if (!isset($paginationBaseUrl, $paginationPage, $paginationTotalPages)) {
    return;
}

$paginationPage       = (int)$paginationPage;
$paginationTotalPages = (int)$paginationTotalPages;

if ($paginationTotalPages <= 1) {
    return; // nothing to show
}

if (!isset($paginationParams) || !is_array($paginationParams)) {
    $paginationParams = [];
}

if (!function_exists('build_pagination_link')) {
    function build_pagination_link($baseUrl, $params, $page)
    {
        $params = $params ?? [];
        $params['page'] = $page;

        $qs = http_build_query($params);
        // if baseUrl already has ?, just append &qs (but we will normally use "index.php" without ?)
        $glue = (strpos($baseUrl, '?') === false) ? '?' : '&';

        return $baseUrl . $glue . $qs;
    }
}
?>

<div class="pagination">
    <!-- Prev -->
    <?php if ($paginationPage > 1): ?>
        <a
            href="<?= htmlspecialchars(build_pagination_link($paginationBaseUrl, $paginationParams, $paginationPage - 1)) ?>"
            class="page-link"
        >Prev</a>
    <?php endif; ?>

    <!-- Pages -->
    <?php for ($i = 1; $i <= $paginationTotalPages; $i++): ?>
        <a
            href="<?= htmlspecialchars(build_pagination_link($paginationBaseUrl, $paginationParams, $i)) ?>"
            class="page-link <?= ($i === $paginationPage) ? 'active-page' : '' ?>"
        ><?= $i ?></a>
    <?php endfor; ?>

    <!-- Next -->
    <?php if ($paginationPage < $paginationTotalPages): ?>
        <a
            href="<?= htmlspecialchars(build_pagination_link($paginationBaseUrl, $paginationParams, $paginationPage + 1)) ?>"
            class="page-link"
        >Next</a>
    <?php endif; ?>
</div>
