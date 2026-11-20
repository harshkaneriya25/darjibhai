<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = $page_title ?? 'Darjibhai';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($page_title) ?> | Darjibhai
    </title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Main CSS -->
    <link
        rel="stylesheet"
        href="/darjibhai/assets/css/style.css"
    >

    <?php if (!empty($admin_page)): ?>

        <!-- Admin Common CSS -->

        <link
            rel="stylesheet"
            href="/darjibhai/assets/css/admin/layout.css"
        >

        <link
            rel="stylesheet"
            href="/darjibhai/assets/css/admin/sidebar.css"
        >

        <link
            rel="stylesheet"
            href="/darjibhai/assets/css/admin/navbar.css"
        >

        <link
            rel="stylesheet"
            href="/darjibhai/assets/css/admin/footer.css"
        >

        <link
            rel="stylesheet"
            href="/darjibhai/assets/css/admin/table.css"
        >

        <link
            rel="stylesheet"
            href="/darjibhai/assets/css/admin/modal.css"
        >

        <link
            rel="stylesheet"
            href="/darjibhai/assets/css/admin/form.css"
        >

        <link rel="stylesheet" href="/darjibhai/assets/css/master/master-layout.css">

    <?php endif; ?>

    <?php if (!empty($extra_css)): ?>

        <?= $extra_css ?>

    <?php endif; ?>

</head>

<body>