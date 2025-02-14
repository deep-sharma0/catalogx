import { useState, useEffect } from 'react';

const Button = (props) => {
    const { customStyle, children, onClick } = props;

    const style = {
        border: '1px solid transparent',
        color: customStyle.button_text_color ?? '#000000',
        backgroundColor: customStyle.button_background_color ?? '#ffffff',
        borderColor: customStyle.button_border_color ?? '#000000',
        borderRadius: customStyle.button_border_radious + 'px' ?? '0px',
        borderWidth: customStyle.button_border_size + 'px' ?? '1px',
        fontSize: customStyle.button_font_size + 'px' ?? '20px',
        fontWeight: customStyle.fontWeight + 'px' ?? '1rem',
        margin: customStyle.button_margin + 'px' ?? '0px',
        padding: customStyle.button_padding + 'px' ?? '0px',
    };

    const hoverStyle = { 
        border: '1px solid transparent',
        color: customStyle.button_text_color_onhover ?? '#000000',
        backgroundColor: customStyle.button_background_color_onhover ?? '#ffffff',
        borderColor: customStyle.button_border_color_onhover ?? '#000000',
        borderRadius: customStyle.button_border_radious + 'px' ?? '0px',
        borderWidth: customStyle.button_border_size + 'px' ?? '1px',
        fontSize: customStyle.button_font_size + 'px' ?? '20px',
        fontWeight: customStyle.fontWeight + 'px' ?? '1rem',
        margin: customStyle.button_margin + 'px' ?? '0px',
        padding: customStyle.button_padding + 'px' ?? '0px',
     };

    const [ hovered, setHovered ] = useState( false );

    return (
        <button
            onMouseEnter={(e) => setHovered(true)}
            onMouseLeave={(e) => setHovered(false)}
            style={hovered ? hoverStyle : style}
            onClick={onClick}
        >
            {customStyle.button_text || children}
        </button>
    );
}

export default Button;