/*Sonradan Eklenen JS kodları bu kısıma eklenesin */
document.querySelector(".menu-toggle").addEventListener("click", function () {
    document.querySelector(".nav-menu").classList.toggle("active");
  });
  


/*--------------------------------------------------------------------------------------*/
/*Admin Sayfası Belge Yükleme Kısmı ile ilgili js kodları */

  let selectedDocuments = []; // Seçilen belgeleri saklayan dizi

  function updateRequiredDocuments() {
    const position = document.getElementById("positionSelect").value;
    const documentsSection = document.getElementById("documentsSection");

    // Eğer pozisyon seçilmişse, belge bölümlerini göster
    if (position) {
      documentsSection.style.display = "block";
    } else {
      documentsSection.style.display = "none";
    }

    // Pozisyona göre zorunlu belgeler
    const documents = {
      doktor: ["Torpil","Diploma", "Uzmanlık Sertifikası", "YDS Sonuç Belgesi"],
      profesor: ["Torpil","A Tipi Makale", "YDS Sonuç Belgesi", "Akademik Deneyim Belgesi"],
      ogretimGorevlisi: ["Torpil","Diploma", "Öğretim Deneyimi Belgesi", "YDS Sonuç Belgesi"],
      stajyer: ["Torpil","Öğrenci Belgesi", "CV", "Staj Kabul Belgesi"],
    };

    // Listeyi temizle
    selectedDocuments = documents[position] ? [...documents[position]] : [];
    renderDocumentList();
  }

  function addExtraDocument() {
    const select = document.getElementById("extraDocumentSelect");
    const selectedValue = select.value;

    if (selectedValue && !selectedDocuments.includes(selectedValue)) {
      selectedDocuments.push(selectedValue);
      renderDocumentList();
    }
  }

  function removeDocument(index) {
    const documentName = selectedDocuments[index];

    // Kullanıcıya onay mesajı göster
    const confirmDelete = confirm(`"${documentName}" belgesini kaldırmak istediğinize emin misiniz?`);

    if (confirmDelete) {
      selectedDocuments.splice(index, 1);
      renderDocumentList();
    }
  }

  function renderDocumentList() {
    const documentsList = document.getElementById("requiredDocumentsList");
    documentsList.innerHTML = "";

    selectedDocuments.forEach((doc, index) => {
      const div = document.createElement("div");
      div.className = "d-flex align-items-center mb-2";

      const label = document.createElement("span");
      label.textContent = doc;
      label.className = "me-3";

      const removeBtn = document.createElement("button");
      removeBtn.className = "btn btn-danger btn-sm";
      removeBtn.innerHTML = "x";
      removeBtn.onclick = () => removeDocument(index);

      div.appendChild(label);
      div.appendChild(removeBtn);

      documentsList.appendChild(div);
    });
  }
  /*------------------------------------------------------------------------------------ */



  
  
/* Bu kısım admin panelindeki başvuru koşullarını belirlemek için kullanıldı */

function addCondition() {
  const conditionsList = document.getElementById("conditionsList");

  // Yeni koşul satırı oluştur
  const conditionRow = document.createElement("div");
  conditionRow.className = "d-flex align-items-center mb-2";

  // Koşul için input alanı
  const input = document.createElement("input");
  input.type = "text";
  input.className = "form-control me-2";
  input.placeholder = "Koşul Giriniz";

  // Ekle/Kaldır butonu
  const button = document.createElement("button");
  button.className = "btn btn-success";
  button.textContent = "Ekle";

  // Butona tıklandığında
  button.onclick = function (event) {
      event.preventDefault(); // Sayfanın yenilenmesini engeller

      if (button.textContent === "Ekle") {
          button.textContent = "Kaldır"; 
          button.className = "btn btn-danger"; 
          addCondition(); // Yeni koşul alanı ekle
      } else {
          conditionRow.remove(); // Satırı kaldır
      }
  };

  // Elemanları div içine ekle
  conditionRow.appendChild(input);
  conditionRow.appendChild(button);
  conditionsList.appendChild(conditionRow);
}

// Sayfa yüklendiğinde ilk koşul giriş alanını oluştur
document.addEventListener("DOMContentLoaded", function () {
  addCondition();
});


/*-------------------------------------------------------------------------------------*/

  
  /*Bu kısımdan altta kalan kısımlar sitenin kendi js kodları */
  
  (function() {
    "use strict";
  
    /**
     * Easy selector helper function
     */
    const select = (el, all = false) => {
      el = el.trim()
      if (all) {
        return [...document.querySelectorAll(el)]
      } else {
        return document.querySelector(el)
      }
    }
  
    /**
     * Easy event listener function
     */
    const on = (type, el, listener, all = false) => {
      if (all) {
        select(el, all).forEach(e => e.addEventListener(type, listener))
      } else {
        select(el, all).addEventListener(type, listener)
      }
    }
  
    /**
     * Easy on scroll event listener 
     */
    const onscroll = (el, listener) => {
      el.addEventListener('scroll', listener)
    }
  
    /**
     * Sidebar toggle
     */
    if (select('.toggle-sidebar-btn')) {
      on('click', '.toggle-sidebar-btn', function(e) {
        select('body').classList.toggle('toggle-sidebar')
      })
    }
  
    /**
     * Search bar toggle
     */
    if (select('.search-bar-toggle')) {
      on('click', '.search-bar-toggle', function(e) {
        select('.search-bar').classList.toggle('search-bar-show')
      })
    }
  
    /**
     * Navbar links active state on scroll
     */
    let navbarlinks = select('#navbar .scrollto', true)
    const navbarlinksActive = () => {
      let position = window.scrollY + 200
      navbarlinks.forEach(navbarlink => {
        if (!navbarlink.hash) return
        let section = select(navbarlink.hash)
        if (!section) return
        if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
          navbarlink.classList.add('active')
        } else {
          navbarlink.classList.remove('active')
        }
      })
    }
    window.addEventListener('load', navbarlinksActive)
    onscroll(document, navbarlinksActive)
  
    /**
     * Toggle .header-scrolled class to #header when page is scrolled
     */
    let selectHeader = select('#header')
    if (selectHeader) {
      const headerScrolled = () => {
        if (window.scrollY > 100) {
          selectHeader.classList.add('header-scrolled')
        } else {
          selectHeader.classList.remove('header-scrolled')
        }
      }
      window.addEventListener('load', headerScrolled)
      onscroll(document, headerScrolled)
    }
  
    /**
     * Back to top button
     */
    let backtotop = select('.back-to-top')
    if (backtotop) {
      const toggleBacktotop = () => {
        if (window.scrollY > 100) {
          backtotop.classList.add('active')
        } else {
          backtotop.classList.remove('active')
        }
      }
      window.addEventListener('load', toggleBacktotop)
      onscroll(document, toggleBacktotop)
    }
  
    /**
     * Initiate tooltips
     */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  
    /**
     * Initiate quill editors
     */
    if (select('.quill-editor-default')) {
      new Quill('.quill-editor-default', {
        theme: 'snow'
      });
    }
  
    if (select('.quill-editor-bubble')) {
      new Quill('.quill-editor-bubble', {
        theme: 'bubble'
      });
    }
  
    if (select('.quill-editor-full')) {
      new Quill(".quill-editor-full", {
        modules: {
          toolbar: [
            [{
              font: []
            }, {
              size: []
            }],
            ["bold", "italic", "underline", "strike"],
            [{
                color: []
              },
              {
                background: []
              }
            ],
            [{
                script: "super"
              },
              {
                script: "sub"
              }
            ],
            [{
                list: "ordered"
              },
              {
                list: "bullet"
              },
              {
                indent: "-1"
              },
              {
                indent: "+1"
              }
            ],
            ["direction", {
              align: []
            }],
            ["link", "image", "video"],
            ["clean"]
          ]
        },
        theme: "snow"
      });
    }
  
    /**
     * Initiate TinyMCE Editor
     */
  
    const useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isSmallScreen = window.matchMedia('(max-width: 1023.5px)').matches;
  
    tinymce.init({
      selector: 'textarea.tinymce-editor',
      plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
      editimage_cors_hosts: ['picsum.photos'],
      menubar: 'file edit view insert format tools table help',
      toolbar: "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl",
      autosave_ask_before_unload: true,
      autosave_interval: '30s',
      autosave_prefix: '{path}{query}-{id}-',
      autosave_restore_when_empty: false,
      autosave_retention: '2m',
      image_advtab: true,
      link_list: [{
          title: 'My page 1',
          value: 'https://www.tiny.cloud'
        },
        {
          title: 'My page 2',
          value: 'http://www.moxiecode.com'
        }
      ],
      image_list: [{
          title: 'My page 1',
          value: 'https://www.tiny.cloud'
        },
        {
          title: 'My page 2',
          value: 'http://www.moxiecode.com'
        }
      ],
      image_class_list: [{
          title: 'None',
          value: ''
        },
        {
          title: 'Some class',
          value: 'class-name'
        }
      ],
      importcss_append: true,
      file_picker_callback: (callback, value, meta) => {
        /* Provide file and text for the link dialog */
        if (meta.filetype === 'file') {
          callback('https://www.google.com/logos/google.jpg', {
            text: 'My text'
          });
        }
  
        /* Provide image and alt text for the image dialog */
        if (meta.filetype === 'image') {
          callback('https://www.google.com/logos/google.jpg', {
            alt: 'My alt text'
          });
        }
  
        /* Provide alternative source and posted for the media dialog */
        if (meta.filetype === 'media') {
          callback('movie.mp4', {
            source2: 'alt.ogg',
            poster: 'https://www.google.com/logos/google.jpg'
          });
        }
      },
      height: 600,
      image_caption: true,
      quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
      noneditable_class: 'mceNonEditable',
      toolbar_mode: 'sliding',
      contextmenu: 'link image table',
      skin: useDarkMode ? 'oxide-dark' : 'oxide',
      content_css: useDarkMode ? 'dark' : 'default',
      content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
    });
  
    /**
     * Initiate Bootstrap validation check
     */
    var needsValidation = document.querySelectorAll('.needs-validation')
  
    Array.prototype.slice.call(needsValidation)
      .forEach(function(form) {
        form.addEventListener('submit', function(event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
  
          form.classList.add('was-validated')
        }, false)
      })
  
    /**
     * Initiate Datatables
     */
    const datatables = select('.datatable', true)
    datatables.forEach(datatable => {
      new simpleDatatables.DataTable(datatable, {
        perPageSelect: [5, 10, 15, ["All", -1]],
        columns: [{
            select: 2,
            sortSequence: ["desc", "asc"]
          },
          {
            select: 3,
            sortSequence: ["desc"]
          },
          {
            select: 4,
            cellClass: "green",
            headerClass: "red"
          }
        ]
      });
    })
  
    /**
     * Autoresize echart charts
     */
    const mainContainer = select('#main');
    if (mainContainer) {
      setTimeout(() => {
        new ResizeObserver(function() {
          select('.echart', true).forEach(getEchart => {
            echarts.getInstanceByDom(getEchart).resize();
          })
        }).observe(mainContainer);
      }, 200);
    }
  
  })();