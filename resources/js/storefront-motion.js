const outsideHero = (element) => !element.closest('.home-hero');
const clamp = (value, minimum, maximum) => Math.min(maximum, Math.max(minimum, value));

function initializeReveals(motionPreference, signal) {
    const elements = [...document.querySelectorAll('[data-reveal]')].filter(outsideHero);
    const showAll = () => elements.forEach((element) => element.classList.add('is-visible'));

    if (!('IntersectionObserver' in window) || motionPreference.matches) {
        showAll();
        return showAll;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
            if (isIntersecting) {
                target.classList.add('is-visible');
                observer.unobserve(target);
            }
        });
    }, { threshold: 0.08 });

    elements.filter((element) => !element.classList.contains('is-visible')).forEach((element) => {
        const siblings = [...element.parentElement.children].filter((child) => child.hasAttribute('data-reveal'));
        element.style.setProperty('--reveal-delay', `${Math.min(siblings.indexOf(element) % 4, 3) * 75}ms`);
        element.classList.add('reveal-ready');
        observer.observe(element);
    });

    motionPreference.addEventListener('change', () => {
        if (motionPreference.matches) {
            observer.disconnect();
            showAll();
        }
    }, { signal });

    return () => {
        observer.disconnect();
        showAll();
    };
}

function initializeParallax(motionPreference, desktopPointer, signal) {
    const frames = new Map();

    [...document.querySelectorAll('[data-parallax]')].filter(outsideHero).forEach((element) => {
        const frame = element.closest('[data-parallax-frame]');
        const amount = Number.parseFloat(element.dataset.parallax);

        if (frame && Number.isFinite(amount)) {
            if (!frames.has(frame)) {
                frames.set(frame, []);
            }
            frames.get(frame).push({ element, amount: clamp(amount, 0, 40) });
        }
    });

    const activeFrames = new Set();
    let updateFrame;
    let enabled = false;

    const reset = () => frames.forEach((items) => items.forEach(({ element }) => element.style.setProperty('--drift-y', '0px')));
    const update = () => {
        updateFrame = undefined;
        const viewportHeight = window.innerHeight;
        const changes = [...activeFrames].flatMap((frame) => {
            const rectangle = frame.getBoundingClientRect();
            const progress = clamp((viewportHeight / 2 - rectangle.top - rectangle.height / 2) / ((viewportHeight + rectangle.height) / 2), -1, 1);
            return frames.get(frame).map(({ element, amount }) => ({ element, offset: progress * amount }));
        });

        changes.forEach(({ element, offset }) => element.style.setProperty('--drift-y', `${offset.toFixed(2)}px`));
    };
    const scheduleUpdate = () => {
        if (enabled && activeFrames.size && updateFrame === undefined) {
            updateFrame = requestAnimationFrame(update);
        }
    };
    const observer = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
            if (isIntersecting && enabled) {
                activeFrames.add(target);
            } else {
                activeFrames.delete(target);
            }
        });
        scheduleUpdate();
    }, { rootMargin: '80px 0px' }) : null;

    const synchronize = () => {
        enabled = desktopPointer.matches && !motionPreference.matches;
        observer?.disconnect();
        activeFrames.clear();
        cancelAnimationFrame(updateFrame);
        updateFrame = undefined;
        reset();

        if (enabled) {
            frames.forEach((items, frame) => observer ? observer.observe(frame) : activeFrames.add(frame));
            scheduleUpdate();
        }
    };

    window.addEventListener('scroll', scheduleUpdate, { passive: true, signal });
    window.addEventListener('resize', scheduleUpdate, { signal });
    motionPreference.addEventListener('change', synchronize, { signal });
    desktopPointer.addEventListener('change', synchronize, { signal });
    synchronize();

    return () => {
        enabled = false;
        observer?.disconnect();
        cancelAnimationFrame(updateFrame);
        activeFrames.clear();
        reset();
    };
}

function initializeCarousel(rail, motionPreference, desktopPointer, signal) {
    const section = rail.closest('.featured-section') ?? rail.parentElement;
    const controls = section?.querySelector('.featured-controls');
    const previousButton = controls?.querySelector('[data-rail-prev]');
    const nextButton = controls?.querySelector('[data-rail-next]');
    const progress = section?.querySelector('[data-rail-progress]');
    const position = section?.querySelector('[data-rail-position]');
    const cards = [...rail.querySelectorAll('.product-card')];
    let updateFrame;
    let drag;
    let suppressClick = false;

    const maximumScroll = () => Math.max(0, rail.scrollWidth - rail.clientWidth);
    const updateControls = () => {
        updateFrame = undefined;
        const maximum = maximumScroll();
        const scrollLeft = clamp(rail.scrollLeft, 0, maximum);
        const railLeft = rail.getBoundingClientRect().left;
        const visibleIndex = cards.findIndex((card) => card.getBoundingClientRect().right > railLeft + 2);

        if (controls) {
            controls.hidden = maximum <= 2;
        }
        if (previousButton) {
            previousButton.disabled = scrollLeft <= 2;
        }
        if (nextButton) {
            nextButton.disabled = scrollLeft >= maximum - 2;
        }
        progress?.style.setProperty('--rail-progress', `${maximum > 2 ? scrollLeft / maximum * 100 : 100}%`);
        if (position) {
            const index = cards.length ? clamp(visibleIndex + 1, 1, cards.length) : 0;
            position.textContent = String(index).padStart(2, '0');
        }
    };
    const scheduleUpdate = () => {
        if (updateFrame === undefined) {
            updateFrame = requestAnimationFrame(updateControls);
        }
    };
    const scrollTo = (left) => rail.scrollTo({
        left: clamp(left, 0, maximumScroll()),
        behavior: motionPreference.matches ? 'auto' : 'smooth',
    });
    const scrollStep = (direction) => {
        if (cards.length) {
            const gap = Number.parseFloat(getComputedStyle(rail).columnGap) || 0;
            scrollTo(rail.scrollLeft + direction * (cards[0].getBoundingClientRect().width + gap));
        }
    };

    previousButton?.addEventListener('click', () => scrollStep(-1), { signal });
    nextButton?.addEventListener('click', () => scrollStep(1), { signal });
    rail.addEventListener('scroll', scheduleUpdate, { passive: true, signal });
    motionPreference.addEventListener('change', () => {
        if (motionPreference.matches) {
            rail.scrollTo({ left: rail.scrollLeft, behavior: 'auto' });
        }
        scheduleUpdate();
    }, { signal });
    rail.addEventListener('keydown', (event) => {
        if (event.target !== rail || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            scrollStep(event.key === 'ArrowLeft' ? -1 : 1);
        } else if (event.key === 'Home' || event.key === 'End') {
            event.preventDefault();
            scrollTo(event.key === 'Home' ? 0 : maximumScroll());
        }
    }, { signal });

    const finishDrag = () => {
        if (!drag) {
            return;
        }

        const finished = drag;
        drag = undefined;
        rail.classList.remove('is-dragging');
        rail.style.scrollSnapType = finished.snapType;
        rail.style.scrollBehavior = finished.scrollBehavior;
        if (rail.hasPointerCapture(finished.pointerId)) {
            rail.releasePointerCapture(finished.pointerId);
        }
        if (finished.moved) {
            suppressClick = true;
        }
        scheduleUpdate();
    };

    rail.addEventListener('pointerdown', (event) => {
        suppressClick = false;
        if (!desktopPointer.matches || event.pointerType !== 'mouse' || event.button !== 0 || event.ctrlKey || event.metaKey || maximumScroll() <= 2) {
            return;
        }
        if (event.target.closest('button, input, textarea, select')) {
            return;
        }
        drag = {
            pointerId: event.pointerId,
            originX: event.clientX,
            scrollLeft: rail.scrollLeft,
            snapType: rail.style.scrollSnapType,
            scrollBehavior: rail.style.scrollBehavior,
            moved: false,
        };
    }, { signal });
    window.addEventListener('pointermove', (event) => {
        if (!drag || event.pointerId !== drag.pointerId) {
            return;
        }
        if (event.buttons === 0) {
            finishDrag();
            return;
        }
        if (Math.abs(event.clientX - drag.originX) < 8 && !drag.moved) {
            return;
        }
        if (!drag.moved) {
            drag.moved = true;
            rail.classList.add('is-dragging');
            rail.style.scrollSnapType = 'none';
            rail.style.scrollBehavior = 'auto';
            rail.setPointerCapture(event.pointerId);
        }
        event.preventDefault();
        rail.scrollLeft = drag.scrollLeft - (event.clientX - drag.originX);
    }, { signal });
    const releasePointer = (event) => {
        if (drag?.pointerId === event.pointerId) {
            finishDrag();
        }
    };
    window.addEventListener('pointerup', releasePointer, { signal });
    window.addEventListener('pointercancel', releasePointer, { signal });
    rail.addEventListener('lostpointercapture', releasePointer, { signal });
    window.addEventListener('blur', finishDrag, { signal });
    desktopPointer.addEventListener('change', finishDrag, { signal });
    rail.addEventListener('dragstart', (event) => {
        if (drag) {
            event.preventDefault();
        }
    }, { signal });
    rail.addEventListener('click', (event) => {
        if (suppressClick && event.detail > 0) {
            event.preventDefault();
            event.stopPropagation();
            suppressClick = false;
        }
    }, { capture: true, signal });

    const resizeObserver = 'ResizeObserver' in window ? new ResizeObserver(scheduleUpdate) : null;
    resizeObserver?.observe(rail);
    cards.forEach((card) => resizeObserver?.observe(card));
    if (!resizeObserver) {
        window.addEventListener('resize', scheduleUpdate, { signal });
    }
    updateControls();

    return () => {
        finishDrag();
        cancelAnimationFrame(updateFrame);
        suppressClick = false;
        resizeObserver?.disconnect();
    };
}

export function initializeStorefrontMotion() {
    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    const desktopPointer = window.matchMedia('(min-width: 1024px) and (hover: hover) and (pointer: fine)');
    let cleanup;

    const start = () => {
        if (cleanup) {
            return;
        }
        const controller = new AbortController();
        const callbacks = [
            initializeReveals(motionPreference, controller.signal),
            initializeParallax(motionPreference, desktopPointer, controller.signal),
            ...[...document.querySelectorAll('.featured-rail')].filter(outsideHero).map((rail) => initializeCarousel(rail, motionPreference, desktopPointer, controller.signal)),
        ];
        cleanup = () => {
            controller.abort();
            callbacks.forEach((callback) => callback());
            cleanup = undefined;
        };
    };

    window.addEventListener('pagehide', () => cleanup?.());
    window.addEventListener('pageshow', start);
    start();
}
