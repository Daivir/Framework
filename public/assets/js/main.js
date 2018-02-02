function button(selector, stimeout = 1.1) {
    let buttons = $(selector);
    $.map(buttons, (button) => {
        let self = $(button);
        self.mousedown((e) => {
            let pos = {x: e.offsetX, y: e.offsetY};
            let effect = $('<div></div>', {class: 'effect'}).css({
                left: pos.x + 'px',
                top: pos.y + 'px'
            });
            self.prepend(effect);
            setTimeout(() => {
                effect.remove();
            }, stimeout * 1000);
        });
    });
}