<!-- Back to Top Button -->
<button data-toggle="back-to-top" class="fixed hidden h-10 w-10 items-center justify-center rounded-full z-10 bottom-20 end-14 p-2.5 bg-primary cursor-pointer shadow-lg text-white">
    <i class="mgc_arrow_up_line text-lg"></i>
</button>

<!-- Theme Settings -->
<div>
    <!-- Theme Setting Button -->
    <div class="fixed end-0 bottom-20">
        <button data-fc-type="offcanvas" data-fc-target="theme-customization" type="button" class="bg-white rounded-s-full shadow-lg p-2.5 ps-3 transition-all dark:bg-slate-800">
            <span class="sr-only">Setting</span>
            <span class="flex items-center justify-center animate-spin">
                <i class="mgc_settings_4_line text-2xl"></i>
            </span>
        </button>
    </div>
    
    <!-- Theme Settings Offcanvas -->
    <div id="theme-customization" class="fc-offcanvas-open:translate-x-0 hidden translate-x-full rtl:-translate-x-full fixed inset-y-0 end-0
         transition-all duration-300 transform max-w-sm w-full z-50 bg-white border-s border-gray-900/10 dark:bg-gray-800 dark:border-white/10" tabindex="-1">
        <div class="h-16 flex items-center text-gray-800 dark:text-white border-b border-dashed border-gray-900/10 dark:border-white/10 px-6 gap-3">
            <h5 class="text-base grow">Theme Settings</h5>
            <button type="button" class="p-2" id="reset-layout"><i class="mgc_refresh_1_line text-xl"></i></button>
            <button type="button" data-fc-dismiss><i class="mgc_close_line text-xl"></i></button>
        </div>
    
        <div class="h-[calc(100vh-64px)]" data-simplebar>
            <div class="divide-y divide-dashed divide-slate-900/10  dark:divide-white/10">
                <div class="p-6">
                    <h5 class="font-semibold text-sm mb-3">Theme</h5>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="card-radio">
                            <input class="form-radio" type="radio" name="data-mode" id="layout-color-light" value="light">
                            <label class="form-label rounded-md" for="layout-color-light">
                                <span class="flex items-center justify-center px-4 py-3">
                                    <i class="mgc_sun_line text-2xl"></i>
                                </span>
                            </label>
                            <div class="mt-1 text-md font-medium text-center text-gray-600 dark:text-gray-300"> Light </div>
                        </div>
    
                        <div class="card-radio">
                            <input class="form-radio" type="radio" name="data-mode" id="layout-color-dark" value="dark">
                            <label class="form-label rounded-md" for="layout-color-dark">
                                <span class="flex items-center justify-center px-4 py-3">
                                    <i class="mgc_moon_line text-2xl"></i>
                                </span>
                            </label>
                            <div class="mt-1 text-md font-medium text-center text-gray-600 dark:text-gray-300"> Dark </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>