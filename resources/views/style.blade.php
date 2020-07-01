<style>
    :root {
        --primary: {{ config('wiki.ui.colors.primary') }};
        --secondary: {{ config('wiki.ui.colors.secondary') }};
    }

    :not(pre)>code[class*=language-], pre[class*=language-] {
        border-top: 3px solid {{ config('wiki.ui.colors.primary') }};
    }

    .bg-gradient-primary {
        background: linear-gradient(87deg, {{ config('wiki.ui.colors.primary') }} 0, {{ config('wiki.ui.colors.secondary') }} 100%) !important;
    }

    [v-cloak] > * {
        display: none;
    }

    [v-cloak]::before {
        content: " ";
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: #F2F6FA;
    }
</style>
