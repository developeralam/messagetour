<form wire:submit="storeEmail">
    <div class="grid grid-cols-1">
        <div class="my-3">
            <p class="form-label font-bold">Write your email</p>
            <div class="form-icon relative mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-mail size-4 absolute top-3 start-4">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                    </path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <input wire:model.defer="email" type="email"
                    class="ps-12 rounded w-full py-2 px-3 h-10 bg-gray-800 border-0 text-gray-100 focus:shadow-none focus:ring-0 placeholder:text-gray-200 outline-none"
                    placeholder="Email" required="">
            </div>
        </div>

        <button type="submit"
            class="py-2 px-5 inline-block font-semibold tracking-wide align-middle duration-500 text-base text-center bg-green-500 hover:bg-green-600 text-white rounded-md">Subscribe</button>
    </div>
</form>
