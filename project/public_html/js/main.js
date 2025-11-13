document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const themeStorageKey = 'salfatex-theme';
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const themeToggleText = themeToggle ? themeToggle.querySelector('[data-theme-toggle-text]') : null;

    const applyTheme = (theme) => {
        const nextTheme = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', nextTheme);
        if (themeToggle) {
            themeToggle.setAttribute('aria-pressed', nextTheme === 'dark' ? 'true' : 'false');
        }
        if (themeToggleText) {
            themeToggleText.textContent = nextTheme === 'dark' ? 'Дневной режим' : 'Ночной режим';
        }
    };

    const storedTheme = localStorage.getItem(themeStorageKey);
    applyTheme(storedTheme === 'dark' ? 'dark' : 'light');

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem(themeStorageKey, nextTheme);
            applyTheme(nextTheme);
        });
    }

    document.querySelectorAll('[data-gallery]').forEach(track => {
        track.addEventListener('wheel', (event) => {
            if (event.deltaY === 0) return;
            event.preventDefault();
            track.scrollBy({ left: event.deltaY, behavior: 'smooth' });
        });
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
});
