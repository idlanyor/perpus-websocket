const currentPage = window.location.pathname.split('/').pop();

const navLinks = document.querySelectorAll('.nav-link');

navLinks.forEach(link => {
    const page = link.getAttribute('data-page');
    
    if (page === currentPage) {
        link.classList.add('bg-blue-100', 'text-blue-600'); 
        link.querySelector('i').classList.add('text-blue-600'); 
        link.querySelector('span').classList.add('text-blue-600'); 
    }
});
