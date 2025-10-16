<!-- Feedback de mensagens -->
@if (session()->has('message'))
<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
    class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
    <p>{{ session('message') }}</p>
</div>
@endif
