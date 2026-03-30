<div class="mb-6 p-4 rounded-lg {{ $type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700' }}">
    <div class="flex justify-between items-center">
        <span>{{ $message }}</span>
        <button class="text-lg font-bold" onclick="this.parentElement.parentElement.style.display='none';">&times;</button>
    </div>
</div>
