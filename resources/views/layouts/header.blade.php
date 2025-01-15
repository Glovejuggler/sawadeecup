<nav class="w-full bg-white p-5 flex justify-center z-50 absolute">
    <div class="max-w-6xl w-full flex justify-between items-center">
        <span class="text-2xl text-green-800">UPTop Diner</span>

        <div class="lg:space-x-8 space-x-4 flex">
            <a href="{{ route('home') }}" class="{{ Route::is('home') ? 'text-green-800 font-bold underline underline-offset-8 decoration-2' : '' }}">Home</a>
            <a href="{{ route('menu') }}" class="{{ Route::is('menu') ? 'text-green-800 font-bold underline underline-offset-8 decoration-2' : '' }}">Menu</a>
            {{-- <a href="{{ route('contacts') }}" class="{{ Route::is('contacts') ? 'text-green-800 font-bold underline underline-offset-8 decoration-2' : '' }}">Contacts</a> --}}
        </div>
    </div>
</nav>
