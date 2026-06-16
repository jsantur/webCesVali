document.addEventListener('DOMContentLoaded', () => {
  // Load Header
  fetch('header.html')
    .then(response => {
      if (!response.ok) throw new Error("Header not found");
      return response.text();
    })
    .then(data => {
      document.getElementById('header-container').innerHTML = data;
      initNavbar();
    })
    .catch(error => console.error("Error loading header:", error));

  // Load Footer
  fetch('footer.html')
    .then(response => {
      if (!response.ok) throw new Error("Footer not found");
      return response.text();
    })
    .then(data => {
      document.getElementById('footer-container').innerHTML = data;
    })
    .catch(error => console.error("Error loading footer:", error));

  // Load Testimonios
  const testimoniosContainer = document.getElementById('testimonios-container');
  if (testimoniosContainer) {
    fetch('testimonios.html')
      .then(response => {
        if (!response.ok) throw new Error("Testimonios not found");
        return response.text();
      })
      .then(data => {
        testimoniosContainer.innerHTML = data;

        fetch('api_testimonios.php')
          .then(r => r.json())
          .then(testData => {
            const track = document.getElementById('dynamic-testimonials-track');
            if (track) {
              if (testData.length === 0) {
                track.innerHTML = '<div style="padding:20px;text-align:center;">Aún no hay testimonios.</div>';
              } else {
                let html = '';
                testData.forEach(t => {
                  let starsHtml = '';
                  for (let i = 1; i <= 5; i++) {
                    let fill = i <= t.estrellas ? '#FDD835' : '#e0e0e0';
                    starsHtml += `<svg viewBox="0 0 24 24" width="14" height="14" fill="${fill}" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>`;
                  }
                  let iconHtml = t.foto ? `<img class="testimonial-icon" src="${t.foto}" alt="${t.nombre}" style="object-fit: cover; border: 2px solid #FDD835; padding: 2px; background: #fff; box-sizing: border-box;">` : `<div class="testimonial-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#1b3140" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-4 4-7 8-7s8 3 8 7"></path></svg></div>`;

                  html += `<div class="testimonial">${iconHtml}<h4>${t.nombre}</h4><p>${t.texto}</p><div class="stars">${starsHtml}</div></div>`;
                });
                track.innerHTML = html;
              }
              initTestimonialsCarousel();
            } else {
              initTestimonialsCarousel();
            }
          })
          .catch(err => {
            console.error("Error cargando testimonios:", err);
            initTestimonialsCarousel();
          });
      })
      .catch(error => console.error("Error loading testimonios:", error));
  }
});

function initNavbar() {
  const links = document.querySelectorAll('.menu a');

  // 1. Marcar el link activo basado en la URL de la página
  let path = window.location.pathname;
  let page = path.split('/').pop();

  // Por defecto a inicio si no hay página específica
  if (page === '' || page === '/') {
    page = 'index.html';
  }

  links.forEach(link => {
    link.classList.remove('active');
    const href = link.getAttribute('href');

    if (href === page) {
      link.classList.add('active');

      // Si el link está dentro de un dropdown, marcar también al padre como activo
      const dropdown = link.closest('.dropdown');
      if (dropdown) {
        const parentLink = dropdown.querySelector('a');
        if (parentLink) {
          parentLink.classList.add('active');
        }
      }
    }
  });

  // 2. Smooth scroll para anclas dentro de la misma página (si las hay)
  links.forEach(link => {
    link.addEventListener('click', (e) => {
      const targetId = link.getAttribute('href');
      // Check if it's an anchor link
      if (targetId && targetId.startsWith('#')) {
        e.preventDefault();
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
          targetSection.scrollIntoView({ behavior: 'smooth' });
        }
      }
    });
  });

  // 3. Hamburger Menu Logic
  const hamburger = document.getElementById('hamburger-menu');
  const menu = document.querySelector('.menu');

  if (hamburger && menu) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      menu.classList.toggle('active');
    });
  }

  // Dropdown toggle en mobile usando el botón .dropbtn
  const dropbtn = document.querySelector('.dropbtn');
  if (dropbtn) {
    dropbtn.addEventListener('click', function (e) {
      e.stopPropagation();
      const dropdown = dropbtn.closest('.dropdown');
      dropdown.classList.toggle('active');
    });

    // Cerrar dropdown al hacer click fuera (respeta el stopPropagation del dropbtn)
    document.addEventListener('click', function (e) {
      const dropdown = document.querySelector('.dropdown');
      if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });
  }
}

function initTestimonialsCarousel() {
  const track = document.querySelector('.testimonials-track');
  const prevBtn = document.querySelector('.testimonials-btn.prev-btn');
  const nextBtn = document.querySelector('.testimonials-btn.next-btn');

  if (track && prevBtn && nextBtn) {
    let position = 0;

    const updateCarousel = () => {
      const card = track.querySelector('.testimonial');
      if (!card) return;

      // Width of a card + gap (20px)
      const cardWidth = card.offsetWidth + 20;
      const containerWidth = document.querySelector('.testimonials-track-wrapper').offsetWidth;

      // Total width of all cards
      const totalWidth = track.scrollWidth;

      // Max position (to not scroll past the end)
      const maxPosition = Math.max(0, totalWidth - containerWidth);

      // Boundaries
      if (position > maxPosition) position = maxPosition;
      if (position < 0) position = 0;

      track.style.transform = `translateX(-${position}px)`;
    };

    nextBtn.addEventListener('click', () => {
      const card = track.querySelector('.testimonial');
      if (!card) return;
      const cardWidth = card.offsetWidth + 20;
      const containerWidth = document.querySelector('.testimonials-track-wrapper').offsetWidth;
      const totalWidth = track.scrollWidth;
      const maxPosition = Math.max(0, totalWidth - containerWidth);

      if (position < maxPosition) {
        position += cardWidth;
        if (position > maxPosition) position = maxPosition;
        updateCarousel();
      }
    });

    prevBtn.addEventListener('click', () => {
      const card = track.querySelector('.testimonial');
      if (!card) return;
      const cardWidth = card.offsetWidth + 20;

      if (position > 0) {
        position -= cardWidth;
        if (position < 0) position = 0;
        updateCarousel();
      }
    });

    window.addEventListener('resize', updateCarousel);
  }
}

// Inicializar el acordeón FAQ
function initFaq() {
  const faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');

    // Al hacer click en la pregunta
    question.addEventListener('click', () => {
      const isActive = item.classList.contains('active');

      // Cerrar todos primero para que solo haya uno abierto a la vez
      faqItems.forEach(faq => faq.classList.remove('active'));

      // Si el item clickeado no estaba activo, lo abrimos
      if (!isActive) {
        item.classList.add('active');
      }
    });
  });
}

// Inicializar la calculadora de préstamos
function initCalculator() {
  const formatCurrency = (amount) => {
    return 'S/ ' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  const setFechaPago = (divFecha) => {
    const today = new Date();
    today.setDate(today.getDate() + 30);
    const dd = String(today.getDate()).padStart(2, '0');
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const yyyy = today.getFullYear();
    divFecha.textContent = dd + '/' + mm + '/' + yyyy;
  };

  // --- LÓGICA CALCULADORA ELECTRO ---
  const inputValorElectro = document.getElementById('calc-valor');
  const divInteresElectro = document.getElementById('calc-interes');
  const divMontoElectro = document.getElementById('calc-monto');
  const divFechaElectro = document.getElementById('calc-fecha');

  if (inputValorElectro && divInteresElectro && divMontoElectro && divFechaElectro) {
    const LTV_PERCENTAGE = 0.60;
    const INTEREST_RATE = 0.129;

    function calculateElectro() {
      const valor = parseFloat(inputValorElectro.value);
      if (isNaN(valor) || valor <= 0) {
        divInteresElectro.textContent = 'S/ 0';
        divMontoElectro.textContent = 'S/ 0';
        return;
      }
      const prestamo = valor;
      const interes = prestamo * INTEREST_RATE;

      divInteresElectro.textContent = formatCurrency(interes);
      divMontoElectro.textContent = formatCurrency(prestamo + interes);
    }

    setFechaPago(divFechaElectro);
    inputValorElectro.addEventListener('input', calculateElectro);
  }

  // --- LÓGICA CALCULADORA JOYAS ---
  const inputPesoJoya = document.getElementById('calc-peso');
  const selectKilates = document.getElementById('calc-kilates');
  const divInteresJoya = document.getElementById('calc-interes-joyas');
  const divMontoJoya = document.getElementById('calc-monto-joyas');
  const divFechaJoya = document.getElementById('calc-fecha-joyas');
  const divPrestamoJoya = document.getElementById('calc-prestamo-joyas');

  if (inputPesoJoya && divInteresJoya && divMontoJoya && divFechaJoya) {

    // Valores por defecto (fallback por si falla el archivo JSON)
    let valoresPorKilate = {
      '14': 1500,
      '18': 500,
      '21': 550,
      '24': 2700
    };
    let interesJoyas = 0.079;

    // Función principal de cálculo
    function calculateJoyas() {
      const peso = parseFloat(inputPesoJoya.value);
      const kilateValue = selectKilates ? valoresPorKilate[selectKilates.value] || 2350 : 2350;

      if (isNaN(peso) || peso <= 0) {
        divInteresJoya.textContent = 'S/ 0.00';
        divMontoJoya.textContent = 'S/ 0.00';
        if (divPrestamoJoya) divPrestamoJoya.textContent = 'S/ 0.00';
        return;
      }

      const prestamo = peso * kilateValue;
      const interes = prestamo * interesJoyas;

      divInteresJoya.textContent = formatCurrency(interes);
      divMontoJoya.textContent = formatCurrency(prestamo + interes);
      if (divPrestamoJoya) divPrestamoJoya.textContent = formatCurrency(prestamo);
    }

    setFechaPago(divFechaJoya);
    inputPesoJoya.addEventListener('input', calculateJoyas);
    if (selectKilates) {
      selectKilates.addEventListener('change', calculateJoyas);
    }

    // Obtener valores dinámicos del servidor
    fetch('data/tasas.json')
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        if (data.tasas_oro_por_gramo) {
          valoresPorKilate = data.tasas_oro_por_gramo;
        }
        if (data.interes_joyas) {
          interesJoyas = data.interes_joyas;
        }
        // Recalcular con los datos reales
        calculateJoyas();
      })
      .catch(error => {
        console.warn('No se pudo cargar tasas.json. Usando valores por defecto.', error);
        calculateJoyas();
      });
  }
}

// Inicializar animaciones de scroll
function initScrollReveal() {
  const revealElements = document.querySelectorAll('.reveal');

  if (revealElements.length === 0) return;

  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.15 // El elemento aparecerá cuando el 15% sea visible
  };

  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
        observer.unobserve(entry.target); // Solo animar una vez
      }
    });
  }, observerOptions);

  revealElements.forEach(el => {
    observer.observe(el);
  });
}

// Si los elementos existen ya al cargar la página, inicializamos
document.addEventListener('DOMContentLoaded', () => {
  initFaq();
  initCalculator();
  initScrollReveal();
});
