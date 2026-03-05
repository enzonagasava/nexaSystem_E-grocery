import Swal, { SweetAlertOptions } from 'sweetalert2'

export function swalFire(options: SweetAlertOptions) {
  return Swal.fire({
    ...options,
    buttonsStyling: false,
    customClass: {
      popup: 'swal2-popup',
      title: 'swal2-title',
      htmlContainer: 'swal2-html-container',
      confirmButton: 'swal2-confirm',
      cancelButton: 'swal2-cancel',
      actions: 'swal2-actions',
      ...(options.customClass || {}),
    },
  })
}

export function swalLoading(title = 'Processando...', allowOutsideClick = false, debug = false) {
  return Swal.fire({
    title,
    width: '320px',
    padding: '0.5rem 0.5rem',
    html: `
      <div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem; width: 100%; height: 100%;">
        <svg
          style="width: 60px; height: 60px; flex-shrink: 0; transform-origin: center;"
          viewBox="0 0 100 100"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          preserveAspectRatio="xMidYMid meet"
        >
          <g>
            <!-- Traço esquerdo do X -->
            <path
              d="M 25 25 L 75 75"
              stroke="currentColor"
              stroke-width="8"
              stroke-linecap="round"
              stroke-dasharray="100"
              stroke-dashoffset="100"
              style="color: var(--primary); animation: swal-draw-stroke 0.6s ease-out forwards, swal-undraw-stroke 0.4s ease-in 0.7s forwards; animation-iteration-count: infinite; animation-delay: 0s, 0.7s;"
            />
            <!-- Traço direito do X -->
            <path
              d="M 75 25 L 25 75"
              stroke="currentColor"
              stroke-width="8"
              stroke-linecap="round"
              stroke-dasharray="100"
              stroke-dashoffset="100"
              style="color: var(--primary); animation: swal-draw-stroke 0.6s ease-out forwards, swal-undraw-stroke 0.4s ease-in 0.7s forwards; animation-iteration-count: infinite; animation-delay: 0.15s, 0.85s;"
            />
          </g>
        </svg>
      </div>
    `,
    buttonsStyling: false,
    showConfirmButton: debug ? true : false,
    showCancelButton: false,
    allowEscapeKey: debug ? false : true,
    customClass: {
      popup: 'swal2-popup swal-loading-popup',
      title: 'swal2-title',
      htmlContainer: 'swal2-html-container',
    },
    didOpen: () => {
      if (!document.getElementById('swal-x-draw')) {
        const style = document.createElement('style')
        style.id = 'swal-x-draw'
        style.textContent = `
          @keyframes swal-draw-stroke {
            0% { stroke-dashoffset: 100; }
            100% { stroke-dashoffset: 0; }
          }
          
          @keyframes swal-undraw-stroke {
            0% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: -100; }
          }
          
          .swal-loading-popup {
            min-height: 300px !important;
            max-width: 85vw !important;
            width: 520px !important;
          }
          
          .swal-loading-popup .swal2-title {
            margin: 0 0 2rem 0 !important;
            font-size: 1.25rem !important;
          }
          
          .swal-loading-popup .swal2-html-container {
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 120px !important;
          }
        `
        document.head.appendChild(style)
      }
      
      // Force scroll to stay visible
      document.documentElement.style.overflowY = 'scroll'
    },
    didClose: () => {
      // Restore normal scroll behavior
      document.documentElement.style.overflowY = ''
    },
    allowOutsideClick: debug ? false : allowOutsideClick,
  })
}

export const swal = { fire: swalFire, loading: swalLoading }

export default swal
