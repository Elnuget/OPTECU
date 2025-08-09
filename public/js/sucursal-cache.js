/**
 * Sistema de caché de sucursales activas
 * Este archivo maneja el almacenamiento y recuperación de la sucursal donde se abrió caja
 */

class SucursalCache {
    static STORAGE_KEY = 'sucursal_abierta';
    static EXPIRY_HOURS = 24; // Las cajas normalmente se cierran en 24 horas

    /**
     * Guarda la sucursal activa en localStorage
     * @param {string} empresaId - ID de la empresa
     * @param {string} empresaNombre - Nombre de la empresa
     */
    static guardar(empresaId, empresaNombre) {
        const sucursalData = {
            id: empresaId,
            nombre: empresaNombre,
            timestamp: new Date().getTime()
        };
        
        try {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(sucursalData));
            console.log('Sucursal guardada en caché:', sucursalData);
            
            // Disparar evento personalizado para que otros componentes puedan reaccionar
            window.dispatchEvent(new CustomEvent('sucursalCacheUpdated', { 
                detail: sucursalData 
            }));
        } catch (e) {
            console.error('Error al guardar sucursal en caché:', e);
        }
    }

    /**
     * Obtiene la sucursal activa del localStorage
     * @returns {Object|null} Datos de la sucursal o null si no existe o ha expirado
     */
    static obtener() {
        try {
            const sucursalData = localStorage.getItem(this.STORAGE_KEY);
            if (!sucursalData) return null;

            const data = JSON.parse(sucursalData);
            
            // Verificar si ha expirado
            const now = new Date().getTime();
            const hoursPassed = (now - data.timestamp) / (1000 * 60 * 60);
            
            if (hoursPassed > this.EXPIRY_HOURS) {
                this.limpiar();
                console.log('Caché de sucursal expirado, limpiando...');
                return null;
            }
            
            return data;
        } catch (e) {
            console.error('Error al obtener sucursal del caché:', e);
            this.limpiar(); // Limpiar datos corruptos
            return null;
        }
    }

    /**
     * Limpia la sucursal activa del localStorage
     */
    static limpiar() {
        try {
            localStorage.removeItem(this.STORAGE_KEY);
            console.log('Caché de sucursal limpiado');
            
            // Disparar evento personalizado
            window.dispatchEvent(new CustomEvent('sucursalCacheCleared'));
        } catch (e) {
            console.error('Error al limpiar caché de sucursal:', e);
        }
    }

    /**
     * Aplica el estilo verde a la sucursal activa en el navbar
     */
    static aplicarEstilosNavbar() {
        const sucursal = this.obtener();
        if (!sucursal) return;

        // Buscar el badge por ID de empresa
        const badge = document.querySelector(`.sucursal-badge[data-empresa-id="${sucursal.id}"]`);
        if (badge) {
            badge.classList.remove('badge-info');
            badge.classList.add('badge-success');
            badge.style.fontWeight = 'bold';
            badge.title = `Caja abierta en: ${sucursal.nombre}`;
            
            // Mover el badge al inicio si hay más de uno
            const parent = badge.parentElement;
            if (parent.children.length > 1) {
                parent.insertBefore(badge, parent.firstChild);
            }
        }
    }

    /**
     * Preselecciona la sucursal activa en un select
     * @param {string} selectId - ID del elemento select
     * @param {boolean} autoSubmit - Si debe enviar el formulario automáticamente
     */
    static preseleccionarEnSelect(selectId, autoSubmit = false) {
        const sucursal = this.obtener();
        if (!sucursal) return;

        const select = document.getElementById(selectId);
        if (!select) return;

        // Verificar que existe la opción
        const option = select.querySelector(`option[value="${sucursal.id}"]`);
        if (!option) return;

        // Solo seleccionar si no hay parámetros GET que indiquen filtro manual
        if (window.location.search.includes('empresa_id=')) return;

        select.value = sucursal.id;
        select.classList.add('filtro-empresa-activo');
        
        console.log(`Sucursal preseleccionada en ${selectId}:`, sucursal.nombre);

        if (autoSubmit && select.form) {
            select.form.submit();
        }
    }

    /**
     * Inicializa todos los componentes relacionados con el caché de sucursales
     */
    static inicializar() {
        // Aplicar estilos al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            this.aplicarEstilosNavbar();
        });

        // También aplicar después de un delay para asegurar que el DOM esté completo
        setTimeout(() => {
            this.aplicarEstilosNavbar();
        }, 500);

        // Escuchar eventos de actualización del caché
        window.addEventListener('sucursalCacheUpdated', () => {
            setTimeout(() => {
                this.aplicarEstilosNavbar();
            }, 100);
        });

        window.addEventListener('sucursalCacheCleared', () => {
            // Limpiar estilos de sucursal activa
            document.querySelectorAll('.sucursal-badge.badge-success').forEach(badge => {
                badge.classList.remove('badge-success');
                badge.classList.add('badge-info');
                badge.style.fontWeight = '';
                badge.title = badge.title.replace('Caja abierta en: ', '');
            });
        });
    }
}

// Inicializar automáticamente
SucursalCache.inicializar();

// Exponer globalmente para uso en otros archivos
window.SucursalCache = SucursalCache;
