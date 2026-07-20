<footer class="footer px-4 mt-auto border-top">
    <div>
        <strong>{{ \App\Models\Setting::get('society_name') ?: 'Society Management System' }}</strong> &copy; {{ date('Y') }}. All rights reserved.
    </div>
    <div class="ms-auto">
        Designed for <strong>{{ \App\Models\Setting::get('society_name') ?: 'SMP' }}</strong>
    </div>
</footer>
