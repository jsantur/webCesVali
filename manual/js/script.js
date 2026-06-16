document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const sections = document.querySelectorAll('.doc-section');
    const navLinks = document.querySelectorAll('.nav-link');
    const noResults = document.getElementById('noResults');
    const themeToggle = document.getElementById('themeToggle');
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    // 1. Tema Claro/Oscuro
    const savedTheme = localStorage.getItem('cesvali-theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('cesvali-theme', newTheme);
        });
    }

    // 2. Menú móvil
    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('open');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });

        overlay.addEventListener('click', () => {
            menuToggle.classList.remove('open');
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }

    // 3. Buscador dinámico: filtra secciones y marca coincidencias
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            // Función para quitar acentos y pasar a minúsculas
            const normalize = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            
            const searchTerm = e.target.value;
            const searchWords = normalize(searchTerm).split(' ').filter(w => w.trim() !== '');

            let hasResults = false;

            sections.forEach(section => {
                const text = normalize(section.innerText);
                const sectionId = section.getAttribute('id');
                const navItem = document.querySelector(`.nav-link[href="#${sectionId}"]`);

                // Comprueba si todas las palabras de búsqueda están en el texto de la sección
                const isMatch = searchWords.every(word => text.includes(word));

                if (isMatch || searchWords.length === 0) {
                    section.style.display = 'block';
                    if (navItem) navItem.style.display = 'flex';
                    section.style.opacity = '1';
                    if (searchWords.length > 0) hasResults = true;
                } else {
                    section.style.display = 'none';
                    if (navItem) navItem.style.display = 'none';
                    section.style.opacity = '0';
                }
            });

            // Mostrar/ocultar mensaje de no resultados
            if (noResults) {
                if (searchWords.length > 0 && !hasResults) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            }
        });
    }

    // 4. Scroll suave al hacer clic en los enlaces y cierre de menú móvil
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId.startsWith('#')) {
                e.preventDefault();
                
                // Resaltado inmediato al hacer clic en el panel
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Ocultar menú móvil si está abierto
                    if (window.innerWidth < 860 && menuToggle && sidebar && overlay) {
                        menuToggle.classList.remove('open');
                        sidebar.classList.remove('open');
                        overlay.classList.remove('open');
                    }

                    // Actualizar URL sin recargar
                    history.pushState(null, null, targetId);

                    // Scroll suave hacia el elemento
                    const headerOffset = 70; // Espacio para el header móvil o márgenes
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.scrollY - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // 3. Resaltado dinámico del menú lateral según el scroll (ScrollSpy personalizado)
    window.addEventListener('scroll', () => {
        let current = '';
        const scrollY = window.scrollY;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            // Si el scroll llega a la sección (con un pequeño offset)
            if (scrollY >= (sectionTop - 150)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });

    // Efecto lupa para imágenes al hacer clic
    const images = document.querySelectorAll('.content-body img');
    
    // Crear el elemento lupa
    const glass = document.createElement('div');
    glass.className = 'img-magnifier-glass';
    glass.style.cssText = `
        position: absolute;
        border: 3px solid #fff;
        border-radius: 50%;
        cursor: none;
        width: 150px;
        height: 150px;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        pointer-events: none;
        z-index: 10000;
        background-repeat: no-repeat;
        transition: opacity 0.2s;
    `;
    document.body.appendChild(glass);

    let activeImage = null;
    const zoomLevel = 2; // Nivel de aumento

    function moveMagnifier(e) {
        if (!activeImage) return;
        
        const imgRect = activeImage.getBoundingClientRect();
        
        let cursorX = e.pageX;
        let cursorY = e.pageY;
        
        let x = e.clientX - imgRect.left;
        let y = e.clientY - imgRect.top;
        
        const w = glass.offsetWidth / 2;
        const h = glass.offsetHeight / 2;
        
        // Ocultar si el cursor sale de la imagen visual
        if (x < 0 || y < 0 || x > activeImage.width || y > activeImage.height) {
            glass.style.opacity = '0';
        } else {
            glass.style.opacity = '1';
        }

        glass.style.left = (cursorX - w) + "px";
        glass.style.top = (cursorY - h) + "px";
        
        let bgX = "-" + ((x * zoomLevel) - w) + "px";
        let bgY = "-" + ((y * zoomLevel) - h) + "px";
        
        glass.style.backgroundPosition = bgX + " " + bgY;
    }

    function disableMagnifier() {
        glass.style.display = 'none';
        if (activeImage) activeImage.style.cursor = 'zoom-in';
        activeImage = null;
        document.removeEventListener('mousemove', moveMagnifier);
    }

    images.forEach(img => {
        img.style.cursor = 'zoom-in';
        
        img.addEventListener('click', function(e) {
            e.stopPropagation();
            if (activeImage === this) {
                disableMagnifier();
            } else {
                if (activeImage) activeImage.style.cursor = 'zoom-in'; // reset anterior
                activeImage = this;
                glass.style.backgroundImage = "url('" + this.src + "')";
                glass.style.backgroundSize = (this.width * zoomLevel) + "px " + (this.height * zoomLevel) + "px";
                glass.style.display = 'block';
                glass.style.opacity = '1';
                this.style.cursor = 'none';
                
                document.addEventListener('mousemove', moveMagnifier);
                moveMagnifier(e); // forzar primera posición
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (activeImage && e.target !== activeImage) {
            disableMagnifier();
        }
    });

    // Añadir botón dinámico de "Volver arriba"
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
    backToTopBtn.className = 'btn-icon'; // Hereda colores y estilo
    backToTopBtn.style.cssText = 'position: fixed; bottom: 30px; right: 30px; display: none; z-index: 9999; width: 44px; height: 44px; border-radius: 50%; box-shadow: var(--shadow-md); opacity: 0; transition: opacity 0.3s, background 0.2s;';
    document.body.appendChild(backToTopBtn);

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.style.display = 'flex';
            setTimeout(() => backToTopBtn.style.opacity = '1', 10);
        } else {
            backToTopBtn.style.opacity = '0';
            setTimeout(() => backToTopBtn.style.display = 'none', 300);
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
