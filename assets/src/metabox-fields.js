import { __ } from '@wordpress/i18n';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useEffect, useState } from '@wordpress/element';
import { TextControl, Button } from '@wordpress/components';

const MprRatingMetaboxFields = () => {

    const [ rowValue, setRowValue] = useState( 0 );
    const [ postRating, setPostRating ] = useState(0);
    const [ message, setMessage ] = useState('');

    useEffect(() => {
        setPostRating( wp.data.select('core/editor').getEditedPostAttribute('meta')['mpr_score'] );
    }, [ setPostRating ])

    const post_id =wp.data.select('core/editor').getCurrentPostId();

    function runApiFetch(a) {

        if ( "" === rowValue ) {
            setMessage( __('Please, check filled fields', 'mpr-likebtn') );
            return;
        }

        let addRating = parseInt(rowValue);

        wp.apiRequest({
            path: 'mpr/v1/custom-rate',
            method: 'POST',
            data: {
                id : post_id,
                rate: addRating
            }
        }).then(data => {
            if ( data.success ) {
                setRowValue(0);
                setPostRating(data.new_rating);
            }
            setMessage( data.message );
        });

    }

    return (
        <PluginDocumentSettingPanel
            name="mpr-panel"
            title={ __('Rating: ', 'mpr-likebtn') + postRating }
            className="mpr-panel"
        >
            <TextControl
                type='number'
                label={ __('Add Rating', 'mpr-likebtn') }
                value={ rowValue }
                onChange={ setRowValue }
            />
            <Button
                className="button button-medium"
                onClick={ runApiFetch }
            >
                {__("Add", 'mpr-likebtn')}
            </Button>
            <p className='mpr-add-rating-result'>{ message }</p>
        </PluginDocumentSettingPanel>
    );
};

export default MprRatingMetaboxFields;