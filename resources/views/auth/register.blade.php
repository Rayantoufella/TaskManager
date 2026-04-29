<x-guest-layout>
    {{-- Logo + tagline --}}
    <div class="text-center mb-8">
        <span class="text-3xl font-extrabold tracking-tight bg-mint-grad bg-clip-text text-transparent">
            TaskFlow
        </span>
        <div class="mt-1 text-xs font-medium text-gray-400 tracking-[0.06em]">
            GÉREZ. ACCOMPLISSEZ. PROGRESSEZ.
        </div>
    </div>

    {{-- Heading --}}
    <div class="mb-7">
        <h1 class="text-[26px] font-extrabold text-mint-900 mb-1">Créez votre compte</h1>
        <p class="text-sm text-gray-500">Commencez à organiser vos tâches dès aujourd'hui </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4" x-data="{ showPass: false }">
        @csrf

        {{-- Name --}}
        <div>
            <label for="name" class="block text-[13px] font-semibold text-mint-900 mb-1.5">Nom complet</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                       placeholder="Rayan Toufella"
                       class="w-full pl-10 pr-3.5 py-3 text-sm text-mint-900 bg-white border-[1.5px] border-mint-200 rounded-xl outline-none focus:border-mint-500 transition-colors">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-[13px] font-semibold text-mint-900 mb-1.5">Adresse email</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                       placeholder="rayan@startup.ma"
                       class="w-full pl-10 pr-3.5 py-3 text-sm text-mint-900 bg-white border-[1.5px] border-mint-200 rounded-xl outline-none focus:border-mint-500 transition-colors">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-[13px] font-semibold text-mint-900 mb-1.5">Mot de passe</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </span>
                <input id="password" name="password" :type="showPass ? 'text' : 'password'" required autocomplete="new-password"
                       placeholder="Minimum 8 caractères"
                       class="w-full pl-10 pr-11 py-3 text-sm text-mint-900 bg-white border-[1.5px] border-mint-200 rounded-xl outline-none focus:border-mint-500 transition-colors">
                <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-mint-500">
                    <svg x-show="!showPass" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="showPass" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm password --}}
        <div>
            <label for="password_confirmation" class="block text-[13px] font-semibold text-mint-900 mb-1.5">Confirmer le mot de passe</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </span>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       placeholder="Répétez le mot de passe"
                       class="w-full pl-10 pr-3.5 py-3 text-sm text-mint-900 bg-white border-[1.5px] border-mint-200 rounded-xl outline-none focus:border-mint-500 transition-colors">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="mt-1 w-full py-3.5 rounded-xl text-white font-semibold text-[15px] bg-mint-grad shadow-glow hover:shadow-glow-hover hover:-translate-y-px active:translate-y-0 transition-all">
            S'inscrire
        </button>

        {{-- Footer --}}
        <p class="text-center text-[13px] text-gray-400">
            Déjà un compte ?
            <a href="{{ route('login') }}" class="text-mint-500 font-semibold hover:text-mint-600">
                Se connecter
            </a>
        </p>
    </form>

    {{-- Security badge --}}
    <div class="mt-6 p-4 bg-mint-50 border-[1.5px] border-mint-200 rounded-xl">
        <div class="flex items-center gap-2 text-xs text-gray-500">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Vos données sont <strong class="text-mint-900">100% sécurisées</strong> et chiffrées</span>
        </div>
    </div>
</x-guest-layout>
