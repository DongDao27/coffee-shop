function setTheme(theme) {
    const dark = theme === 'theme-dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('theme-dark', dark);
    document.documentElement.classList.toggle('theme-light', !dark);
    document.body.classList.toggle('bg-background', dark);
}
setTheme(window.theme || 'auto');
window.addEventListener('theme-changed', event => {
    window.theme = event.detail.theme;
    setTheme(window.theme);
});
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (window.theme === 'auto') setTheme('auto');
});
