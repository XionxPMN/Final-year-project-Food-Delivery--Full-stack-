let currentLang = 'en';

function toggleLanguage() {
    const elements = document.querySelectorAll('.translatable');
    const toggleBtn = document.getElementById('langToggle');
    const iconSvg = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>`;

    if (currentLang === 'en') {
        elements.forEach(el => el.innerText = el.getAttribute('data-my'));
        toggleBtn.innerHTML = `${iconSvg} English`;
        currentLang = 'my';
    } else {
        elements.forEach(el => el.innerText = el.getAttribute('data-en'));
        toggleBtn.innerHTML = `${iconSvg} မြန်မာ`;
        currentLang = 'en';
    }
}