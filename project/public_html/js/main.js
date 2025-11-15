document.addEventListener('DOMContentLoaded', () => {
    const headerNav = document.querySelector('nav[aria-label="Главное меню"]');
    const rootElement = document.documentElement;

    const updateHeaderOffset = () => {
        if (!headerNav) return;
        const height = headerNav.offsetHeight;
        if (height > 0) {
            rootElement.style.setProperty('--header-height', `${height}px`);
        }
    };

    updateHeaderOffset();
    window.addEventListener('load', updateHeaderOffset, { once: true });
    window.addEventListener('resize', updateHeaderOffset);
    document.querySelectorAll('[data-gallery]').forEach(track => {
        track.addEventListener('wheel', (event) => {
            if (event.deltaY === 0) return;
            event.preventDefault();
            track.scrollBy({ left: event.deltaY, behavior: 'smooth' });
        });
    });

    document.querySelectorAll('[data-slider]').forEach(slider => {
        const track = slider.querySelector('[data-slider-track]');
        const dotsContainer = slider.querySelector('[data-slider-dots]');
        const slides = track ? Array.from(track.children) : [];
        if (!track || !slides.length) return;

        const interval = Number(slider.dataset.sliderInterval) || 5000;
        let activePage = 0;
        let timerId = null;
        let dots = [];
        let totalPages = 1;
        let resizeTimer = null;

        const getSlidesPerView = () => {
            const cssValue = parseFloat(getComputedStyle(slider).getPropertyValue('--slides-per-view'));
            if (!Number.isFinite(cssValue) || cssValue <= 0) {
                return 1;
            }
            return Math.min(slides.length, Math.max(1, Math.round(cssValue)));
        };

        const updateSlides = () => {
            track.style.transform = `translateX(-${activePage * 100}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('is-active', index === activePage);
            });
        };

        const goToPage = (nextPage) => {
            activePage = (nextPage + totalPages) % totalPages;
            updateSlides();
        };

        const stopAuto = () => {
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
        };

        const startAuto = () => {
            if (totalPages <= 1) return;
            stopAuto();
            timerId = setInterval(() => {
                goToPage(activePage + 1);
            }, interval);
        };

        const buildDots = () => {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            if (totalPages <= 1) {
                dotsContainer.style.display = 'none';
                dots = [];
                return;
            }

            dotsContainer.style.display = 'flex';
            for (let page = 0; page < totalPages; page += 1) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'gallery-slider__dot';
                dot.setAttribute('aria-label', `Показать слайды ${page + 1}`);
                dot.addEventListener('click', () => {
                    goToPage(page);
                    startAuto();
                });
                dotsContainer.appendChild(dot);
            }
            dots = Array.from(dotsContainer.children);
        };

        const refreshMetrics = () => {
            const slidesPerView = getSlidesPerView();
            totalPages = Math.max(1, Math.ceil(slides.length / slidesPerView));
            activePage = Math.min(activePage, totalPages - 1);
            buildDots();
            updateSlides();
            startAuto();
        };

        const scheduleRefresh = () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(refreshMetrics, 150);
        };

        slider.addEventListener('mouseenter', stopAuto);
        slider.addEventListener('mouseleave', startAuto);
        window.addEventListener('resize', scheduleRefresh);

        refreshMetrics();
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (event) => {
            const targetId = anchor.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            if (target) {
                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    const siteNav = document.querySelector('[data-site-nav]');
    const navToggles = document.querySelectorAll('[data-nav-toggle]');
    const body = document.body;

    if (siteNav && navToggles.length) {
        const setNavState = (shouldOpen) => {
            siteNav.classList.toggle('hidden', !shouldOpen);
            body.classList.toggle('nav-open', shouldOpen);
            navToggles.forEach(btn => {
                btn.classList.toggle('is-active', shouldOpen);
                btn.setAttribute('aria-expanded', String(shouldOpen));
            });
            requestAnimationFrame(updateHeaderOffset);
        };

        navToggles.forEach(button => {
            button.addEventListener('click', () => {
                const isHidden = siteNav.classList.contains('hidden');
                setNavState(isHidden);
            });
        });

        siteNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => setNavState(false));
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                setNavState(false);
            }
        });
    }

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (event) => {
            const phone = contactForm.querySelector('input[name="phone"]').value.trim();
            const email = contactForm.querySelector('input[name="email"]').value.trim();
            if (phone.length < 5 || !email.includes('@')) {
                event.preventDefault();
                alert('Проверьте корректность телефона и email.');
            }
        });
    }

    document.querySelectorAll('[data-table-editor]').forEach(editor => {
        const columnsContainer = editor.querySelector('[data-columns]');
        const rowsContainer = editor.querySelector('[data-rows]');
        const addColumnBtn = editor.querySelector('[data-add-column]');
        const addRowBtn = editor.querySelector('[data-add-row]');

        const getColumnCount = () => columnsContainer.querySelectorAll('.column-input').length;
        const getRowCount = () => rowsContainer.querySelectorAll('.row-block').length;

        const rebuildCells = () => {
            const columnCount = getColumnCount();
            rowsContainer.querySelectorAll('.row-block').forEach(row => {
                const rowIndex = row.dataset.index;
                let cellWrapper = row.querySelector('.cells');
                if (!cellWrapper) {
                    cellWrapper = document.createElement('div');
                    cellWrapper.className = 'cells';
                    row.appendChild(cellWrapper);
                }
                const existing = cellWrapper.querySelectorAll('.cell-input');
                if (existing.length) {
                    const stored = [];
                    existing.forEach((cell, index) => {
                        stored[index] = cell.value;
                    });
                    row.dataset.values = JSON.stringify(stored);
                }
                existing.forEach(cell => cell.remove());
                for (let i = 0; i < columnCount; i++) {
                    const cell = document.createElement('textarea');
                    cell.className = 'cell-input';
                    cell.name = `cells[${rowIndex}][${i}]`;
                    cell.placeholder = `Значение ${i + 1}`;
                    cellWrapper.appendChild(cell);
                }
                if (row.dataset.values) {
                    try {
                        const values = JSON.parse(row.dataset.values);
                        cellWrapper.querySelectorAll('.cell-input').forEach((textarea, i) => {
                            textarea.value = values[i] || '';
                        });
                    } catch (error) {
                        console.warn('Invalid row values', error);
                    }
                }
            });
        };

        const addColumn = (value = '') => {
            const index = getColumnCount();
            const wrapper = document.createElement('div');
            wrapper.className = 'column-input';
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'columns[]';
            input.placeholder = `Колонка ${index + 1}`;
            input.value = value;
            wrapper.appendChild(input);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                wrapper.remove();
                rebuildCells();
            });
            wrapper.appendChild(remove);
            columnsContainer.appendChild(wrapper);
            rebuildCells();
        };

        const addRow = (values = []) => {
            const index = getRowCount();
            const rowBlock = document.createElement('div');
            rowBlock.className = 'row-block';
            rowBlock.dataset.index = index;
            rowBlock.dataset.values = JSON.stringify(values);
            const label = document.createElement('input');
            label.type = 'hidden';
            label.name = 'rows[]';
            label.value = index;
            rowBlock.appendChild(label);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = 'Удалить строку';
            remove.addEventListener('click', () => {
                rowBlock.remove();
            });
            rowBlock.appendChild(remove);
            rowsContainer.appendChild(rowBlock);
            rebuildCells();
        };

        const bindExistingControls = () => {
            columnsContainer.querySelectorAll('.column-input button').forEach(btn => {
                if (btn.dataset.bound) return;
                btn.dataset.bound = '1';
                btn.addEventListener('click', () => {
                    const wrapper = btn.closest('.column-input');
                    if (wrapper) {
                        wrapper.remove();
                        rebuildCells();
                    }
                });
            });
            rowsContainer.querySelectorAll('.row-block button').forEach(btn => {
                if (btn.dataset.bound) return;
                btn.dataset.bound = '1';
                btn.addEventListener('click', () => {
                    const block = btn.closest('.row-block');
                    if (block) {
                        block.remove();
                    }
                });
            });
        };

        if (addColumnBtn) {
            addColumnBtn.addEventListener('click', () => addColumn());
        }
        if (addRowBtn) {
            addRowBtn.addEventListener('click', () => addRow());
        }

        if (!getColumnCount()) {
            addColumn('Показатель');
            addColumn('Значение');
        }
        if (!getRowCount()) {
            addRow();
        }
        bindExistingControls();
        rebuildCells();
    });

    const hasCropper = typeof Cropper !== 'undefined';
    const cropperModal = document.getElementById('imageCropModal');
    if (hasCropper && cropperModal) {
        const modalImage = cropperModal.querySelector('[data-crop-image]');
        const confirmBtn = cropperModal.querySelector('[data-crop-confirm]');
        const skipBtn = cropperModal.querySelector('[data-crop-skip]');
        const cancelButtons = cropperModal.querySelectorAll('[data-crop-cancel]');
        const fileNameTarget = cropperModal.querySelector('[data-crop-filename]');
        let cropperInstance = null;
        let resolver = null;
        let rejecter = null;
        let currentObjectUrl = null;
        let activeFile = null;

        const toggleBodyScroll = (lock) => {
            document.body.classList.toggle('cropper-open', !!lock);
        };

        const destroyCropper = () => {
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }
        };

        const closeCropperModal = () => {
            destroyCropper();
            cropperModal.setAttribute('hidden', 'hidden');
            cropperModal.classList.remove('is-open');
            toggleBodyScroll(false);
            if (modalImage) {
                modalImage.removeAttribute('src');
                modalImage.onload = null;
            }
            activeFile = null;
            resolver = null;
            rejecter = null;
        };

        const openCropperModal = (file) => new Promise((resolve, reject) => {
            resolver = resolve;
            rejecter = reject;
            activeFile = file;
            if (!modalImage) {
                resolve(null);
                return;
            }
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
            }
            currentObjectUrl = URL.createObjectURL(file);
            if (fileNameTarget) {
                fileNameTarget.textContent = file.name || '';
            }
            cropperModal.removeAttribute('hidden');
            cropperModal.classList.add('is-open');
            toggleBodyScroll(true);
            modalImage.onload = () => {
                cropperInstance = new Cropper(modalImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.92,
                    background: false,
                    movable: true,
                    responsive: true,
                });
            };
            modalImage.src = currentObjectUrl;
        });

        const handleCancel = () => {
            if (rejecter) {
                rejecter(new Error('cancelled'));
            }
            closeCropperModal();
        };

        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                if (!cropperInstance || !resolver) {
                    return;
                }
                const canvas = cropperInstance.getCroppedCanvas();
                if (!canvas) {
                    resolver(null);
                    closeCropperModal();
                    return;
                }
                const preferredType = (activeFile && /^image\/[-+\.\w]+$/i.test(activeFile.type)) ? activeFile.type : 'image/png';
                let dataUrl = null;
                try {
                    dataUrl = canvas.toDataURL(preferredType, 1);
                } catch (error) {
                    console.warn('Unable to export cropped image', error);
                }
                resolver(dataUrl ? {
                    dataUrl,
                    mimeType: preferredType,
                    originalName: activeFile ? activeFile.name : 'image',
                } : null);
                closeCropperModal();
            });
        }

        if (skipBtn) {
            skipBtn.addEventListener('click', () => {
                if (!resolver) {
                    return;
                }
                resolver(null);
                closeCropperModal();
            });
        }

        cancelButtons.forEach((btn) => {
            btn.addEventListener('click', handleCancel);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && cropperModal.classList.contains('is-open')) {
                handleCancel();
            }
        });

        const dataUrlToFile = (dataUrl, originalName, mimeType) => {
            if (!dataUrl) {
                return null;
            }
            const parts = dataUrl.split(',');
            if (parts.length < 2) {
                return null;
            }
            const byteString = atob(parts[1]);
            const array = new Uint8Array(byteString.length);
            for (let i = 0; i < byteString.length; i += 1) {
                array[i] = byteString.charCodeAt(i);
            }
            const fallbackType = mimeType || (parts[0].match(/data:(.*);base64/i)?.[1]) || 'image/png';
            const extension = fallbackType.split('/')[1] || 'png';
            const baseName = originalName && originalName.includes('.')
                ? originalName.slice(0, originalName.lastIndexOf('.'))
                : (originalName || 'image');
            const safeExt = extension.replace(/[^a-z0-9]+/gi, '') || 'png';
            return new File([array], `${baseName || 'image'}-cropped.${safeExt}`, { type: fallbackType });
        };

        const processInputFiles = async (input) => {
            const files = Array.from(input.files || []);
            if (!files.length || typeof DataTransfer === 'undefined') {
                return;
            }
            const transfer = new DataTransfer();
            for (const file of files) {
                let nextFile = file;
                try {
                    const result = await openCropperModal(file);
                    if (result && result.dataUrl) {
                        const converted = dataUrlToFile(result.dataUrl, result.originalName || file.name, result.mimeType);
                        if (converted) {
                            nextFile = converted;
                        }
                    }
                } catch (error) {
                    console.warn('Cropping cancelled', error);
                    input.value = '';
                    return;
                }
                transfer.items.add(nextFile);
            }
            input.files = transfer.files;
        };

        document.querySelectorAll('input[type="file"][data-crop-field]').forEach((input) => {
            input.addEventListener('change', () => {
                processInputFiles(input);
            });
        });
    }
});
