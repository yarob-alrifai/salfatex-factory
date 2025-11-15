<?php
function render_admin_theme_assets(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    ?>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            theme: {
                fontFamily: {
                    sans: ['Inter', 'Cairo', 'Segoe UI', 'sans-serif'],
                },
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                        },
                    },
                },
            },
        };
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <style type="text/tailwindcss">
        @layer base {
            body.admin-body {
                @apply font-sans transition-colors duration-300;
            }

            body.admin-body.theme-dark {
                @apply bg-slate-950 text-slate-100;
            }

            body.admin-body.theme-light {
                @apply bg-slate-100 text-slate-900;
            }

            body.admin-auth {
                @apply bg-slate-950 text-slate-100 font-sans;
            }
        }

        @layer components {
            .admin-app {
                @apply min-h-screen bg-slate-950 flex flex-col lg:flex-row;
            }

            .admin-sidebar {
                @apply w-full lg:w-72 xl:w-80 border-b border-slate-800/80 bg-slate-900/70 px-6 py-8 text-slate-100 flex flex-col gap-8 backdrop-blur lg:border-b-0 lg:border-r;
            }

            .admin-brand {
                @apply flex items-center gap-4;
            }

            .admin-brand span {
                @apply flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500/40 to-sky-400/30 text-2xl font-bold text-white;
            }

            .admin-brand strong {
                @apply block text-xl;
            }

            .admin-brand small {
                @apply block text-xs font-normal text-slate-400;
            }

            .admin-user {
                @apply rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-inner shadow-slate-900/40;
            }

            .admin-user p {
                @apply text-sm text-slate-400;
            }

            .admin-user strong {
                @apply text-lg font-semibold text-white;
            }

            .admin-menu {
                @apply flex flex-col gap-1;
            }

            .admin-menu__link {
                @apply flex items-center justify-between rounded-2xl px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-slate-800/70;
            }

            .admin-menu__link.is-active {
                @apply bg-white text-slate-900 shadow-lg shadow-slate-900/20;
            }

            .admin-menu__link.is-destructive {
                @apply text-rose-300 hover:bg-rose-500/10 hover:text-rose-100;
            }

            .admin-main {
                @apply flex-1 min-h-screen bg-slate-900/30 p-6 lg:p-10;
            }

            .admin-topbar {
                @apply flex flex-col gap-4 rounded-3xl border border-slate-800 bg-slate-900/70 p-6 shadow-2xl shadow-slate-900/30 backdrop-blur lg:flex-row lg:items-center lg:justify-between;
            }

            .admin-topbar h1 {
                @apply text-3xl font-bold text-white;
            }

            .admin-topbar__meta {
                @apply flex flex-col gap-3 text-right lg:items-end;
            }

            .eyebrow {
                @apply text-xs uppercase tracking-[0.3em] text-slate-500;
            }

            .subtitle {
                @apply text-base text-slate-400;
            }

            .badge {
                @apply inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold;
            }

            .badge--online {
                @apply bg-emerald-500/15 text-emerald-200;
            }

            .admin-theme-toggle {
                @apply inline-flex items-center gap-3 rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-2 text-sm font-semibold text-slate-100 shadow-lg shadow-slate-900/30 transition hover:border-slate-700 hover:bg-slate-900;
            }

            .admin-theme-toggle__icon {
                @apply text-lg;
            }

            .admin-panel {
                @apply mt-8 rounded-3xl border border-slate-800/80 bg-slate-900/80 p-6 shadow-2xl shadow-slate-900/20;
            }

            .admin-panel form {
                @apply space-y-5;
            }

            .admin-panel label {
                @apply flex flex-col gap-2 text-sm font-semibold text-slate-100;
            }

            .admin-panel input,
            .admin-panel textarea,
            .admin-panel select {
                @apply w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-base text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:ring focus:ring-blue-500/30;
            }

            .admin-panel button,
            .admin-panel .btn {
                @apply inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-sky-500 px-5 py-3 text-base font-semibold text-white shadow-lg shadow-blue-600/40 transition hover:from-blue-500 hover:to-sky-400;
            }

            .admin-panel .btn-secondary {
                @apply inline-flex items-center justify-center rounded-2xl border border-slate-700/70 bg-transparent px-5 py-3 text-base font-semibold text-slate-100 transition hover:border-slate-500 hover:text-white;
            }

            .admin-panel .btn-danger {
                @apply inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-rose-600 to-pink-500 px-5 py-3 text-base font-semibold text-white shadow-lg shadow-rose-600/40 transition hover:from-rose-500 hover:to-pink-400;
            }

            .admin-panel .btn-small {
                @apply rounded-xl px-3 py-2 text-sm;
            }

            .stats-grid {
                @apply grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5;
            }

            .stats-grid > div {
                @apply rounded-2xl border border-slate-800 bg-slate-900/60 p-4 text-center text-lg font-semibold text-white shadow shadow-slate-900/40;
            }

            .admin-table {
                @apply mt-6 w-full border-collapse overflow-hidden rounded-3xl text-sm;
            }

            .admin-table thead {
                @apply bg-slate-900/70;
            }

            .admin-table th {
                @apply border-b border-slate-800 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400;
            }

            .admin-table td {
                @apply border-b border-slate-800 px-4 py-3 text-slate-100;
            }

            .admin-table tbody tr:hover {
                @apply bg-slate-900/50;
            }

            .admin-table a {
                @apply text-blue-300 hover:text-blue-200;
            }

            .admin-table .table-actions {
                @apply flex flex-wrap gap-2;
            }

            .admin-table .table-actions .btn-secondary,
            .admin-table .table-actions .btn-danger {
                @apply w-auto;
            }

            .message-details {
                @apply space-y-6;
            }

            .message-actions {
                @apply flex flex-wrap items-center justify-end gap-3;
            }

            .message-details__grid {
                @apply mt-6 grid gap-4 lg:grid-cols-2;
            }

            .message-details__item {
                @apply rounded-3xl border border-slate-800 bg-slate-950/50 p-5;
            }

            .message-details__label {
                @apply text-xs font-semibold uppercase tracking-widest text-slate-400;
            }

            .message-details__list {
                @apply mt-4 space-y-3 text-sm text-slate-100;
            }

            .message-details__list li {
                @apply flex flex-col gap-1;
            }

            .message-details__list span {
                @apply text-xs uppercase tracking-wide text-slate-400;
            }

            .message-details__list a {
                @apply text-blue-300 hover:text-blue-200;
            }

            .message-body {
                @apply rounded-3xl border border-slate-800 bg-slate-950/40 p-5;
            }

            .message-body__content {
                @apply mt-3 whitespace-pre-line text-base leading-8 text-slate-100;
            }

            .table-editor {
                @apply mt-6 space-y-4 rounded-3xl border border-slate-800 bg-slate-900/70 p-5;
            }

            .table-editor .columns,
            .table-editor .rows {
                @apply space-y-3;
            }

            .table-editor .column-input,
            .table-editor .row-block {
                @apply flex flex-col gap-3 rounded-2xl border border-dashed border-slate-700 bg-slate-950/40 p-4 sm:flex-row sm:items-center;
            }

            .table-editor .cells {
                @apply grid gap-3;
            }

            .table-editor textarea {
                @apply min-h-[60px] rounded-2xl border border-slate-800 bg-slate-950/60 p-3 text-slate-100;
            }

            body.admin-body.theme-light .admin-app {
                @apply bg-slate-50 text-slate-900;
            }

            body.admin-body.theme-light .admin-sidebar {
                @apply border-slate-200 bg-white text-slate-900;
            }

            body.admin-body.theme-light .admin-user {
                @apply border-slate-200 bg-slate-50 text-slate-900;
            }

            body.admin-body.theme-light .admin-user p {
                @apply text-slate-500;
            }

            body.admin-body.theme-light .admin-user strong {
                @apply text-slate-900;
            }

            body.admin-body.theme-light .admin-menu__link {
                @apply text-slate-700 hover:bg-slate-100;
            }

            body.admin-body.theme-light .admin-menu__link.is-active {
                @apply bg-slate-900 text-white shadow-lg shadow-slate-300;
            }

            body.admin-body.theme-light .admin-menu__link.is-destructive {
                @apply text-rose-600 hover:bg-rose-50 hover:text-rose-700;
            }

            body.admin-body.theme-light .admin-main {
                @apply bg-slate-100;
            }

            body.admin-body.theme-light .admin-topbar {
                @apply border-slate-200 bg-white text-slate-900 shadow-slate-200;
            }

            body.admin-body.theme-light .admin-topbar h1 {
                @apply text-slate-900;
            }

            body.admin-body.theme-light .eyebrow {
                @apply text-slate-500;
            }

            body.admin-body.theme-light .subtitle {
                @apply text-slate-500;
            }

            body.admin-body.theme-light .badge--online {
                @apply bg-emerald-100 text-emerald-700;
            }

            body.admin-body.theme-light .admin-theme-toggle {
                @apply border-slate-200 bg-white text-slate-800 shadow-slate-200;
            }

            body.admin-body.theme-light .admin-panel {
                @apply border-slate-200 bg-white text-slate-900 shadow-slate-200;
            }

            body.admin-body.theme-light .admin-panel label {
                @apply text-slate-900;
            }

            body.admin-body.theme-light .admin-panel input,
            body.admin-body.theme-light .admin-panel textarea,
            body.admin-body.theme-light .admin-panel select {
                @apply border-slate-200 bg-white text-slate-900 placeholder-slate-400;
            }

            body.admin-body.theme-light .admin-panel button,
            body.admin-body.theme-light .admin-panel .btn {
                @apply shadow-blue-200/70;
            }

            body.admin-body.theme-light .stats-grid > div {
                @apply border-slate-200 bg-white text-slate-900 shadow-slate-200;
            }

            body.admin-body.theme-light .admin-table thead {
                @apply bg-slate-100;
            }

            body.admin-body.theme-light .admin-table th {
                @apply border-slate-200 text-slate-500;
            }

            body.admin-body.theme-light .admin-table td {
                @apply border-slate-200 text-slate-800;
            }

            body.admin-body.theme-light .admin-table tbody tr:hover {
                @apply bg-slate-50;
            }

            body.admin-body.theme-light .admin-table a {
                @apply text-blue-600 hover:text-blue-700;
            }

            body.admin-body.theme-light .table-editor {
                @apply border-slate-200 bg-white;
            }

            body.admin-body.theme-light .table-editor .column-input,
            body.admin-body.theme-light .table-editor .row-block {
                @apply border-slate-200 bg-slate-50;
            }

            body.admin-body.theme-light .table-editor textarea {
                @apply border-slate-200 bg-white text-slate-900;
            }

            .btn--primary {
                @apply inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-sky-500 px-6 py-3 font-semibold text-white shadow-lg shadow-blue-600/40 transition hover:from-blue-500 hover:to-sky-400;
            }

            .admin-alert {
                @apply rounded-2xl border border-rose-600/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-100;
            }

            .admin-alert--error {
                @apply border-rose-500/40 bg-rose-500/20 text-rose-50;
            }

            .admin-auth {
                @apply min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 px-6 py-12;
            }

            .admin-auth__shell {
                @apply mx-auto grid w-full max-w-6xl gap-8 items-center lg:grid-cols-2;
            }

            .admin-auth__intro {
                @apply rounded-[32px] border border-white/10 bg-white/5 p-8 text-white shadow-2xl shadow-slate-950/40 backdrop-blur;
            }

            .admin-auth__intro h1 {
                @apply mt-2 text-3xl font-bold text-white;
            }

            .admin-auth__intro p {
                @apply text-slate-200/90 leading-relaxed;
            }

            .admin-auth__stats {
                @apply mt-8 grid gap-4 text-sm text-white sm:grid-cols-2;
            }

            .admin-auth__stats li {
                @apply rounded-2xl border border-white/15 bg-white/10 p-4;
            }

            .admin-auth__stats strong {
                @apply text-base font-semibold;
            }

            .admin-auth__stats span {
                @apply text-sm text-slate-200/80;
            }

            .admin-auth__card {
                @apply rounded-[32px] bg-white p-8 text-slate-900 shadow-2xl shadow-slate-900/30;
            }

            .admin-auth__logo {
                @apply mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-xl font-bold text-blue-600;
            }

            .admin-auth__heading h2 {
                @apply text-2xl font-bold text-slate-900;
            }

            .admin-form {
                @apply mt-6 flex flex-col gap-4;
            }

            .admin-form label {
                @apply flex flex-col gap-2 text-sm font-semibold text-slate-700;
            }

            .admin-form input {
                @apply rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-blue-500 focus:ring focus:ring-blue-500/20;
            }
        }
    </style>
    <?php
}
