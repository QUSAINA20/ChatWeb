<div x-data="{ isOpen: false, message: '', sender: '' }" x-show.transition="isOpen"
    class="fixed top-0 left-0 right-0 p-4 bg-blue-500 text-white shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <span x-text="sender"></span>
        </div>
        <div>
            <button @click="isOpen = false" class="text-lg">×</button>
        </div>
    </div>
    <div class="mt-2">
        <p x-text="message"></p>
    </div>
</div>
