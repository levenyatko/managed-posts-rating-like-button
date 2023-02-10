import { registerPlugin } from '@wordpress/plugins';

import MprRatingMetaboxFields from './metabox-fields';

registerPlugin( 'mprating', {
    render() {
        return( <MprRatingMetaboxFields label='Rating' /> );
    }
} );