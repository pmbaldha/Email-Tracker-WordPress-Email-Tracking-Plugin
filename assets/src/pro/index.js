import { __, _n } from '@wordpress/i18n';
import { PanelRow, PanelBody } from '@wordpress/components';


const { addFilter } = wp.hooks;
import moment from 'moment';

addFilter('email-tracker-view-email-extra-panel', 'email-tracker/add-click-panel', ( ret, state ) => {
    let click_log_panel_body;
    if ( state.total_link_click_count > 0 ) {
        click_log_panel_body = (
            <PanelRow>
                <ol>
                { state.link_click_log.map( link_click_log => {
                        if ( link_click_log.date_time ) {
                            let click_local_moment = moment.utc( link_click_log.date_time ).local();
                            return (
                                <li>
                                    {sprintf( '"%s" clicked at %s on IP %s', link_click_log.link, click_local_moment.format( 'MMMM Do YYYY, dddd, h:mm:ss a' ) + ' (' + click_local_moment.fromNow() + ')', link_click_log.ip_address ) }
                                </li>
                            );
                        } else {
                            return ( null );
                        }
                    }
                    )
                }
                </ol>
            </PanelRow>
            );
    } else {
        click_log_panel_body = __( 'N/A', 'email-tracker' );
    }
    return (<PanelBody title={ "#" + sprintf( _n( '%d time clicked', '%d times clicked', parseInt( state.total_link_click_count ), 'email-tracker' ), state.total_link_click_count ) } initialOpen={ state.link_click_log.length ? true : false } >
                { click_log_panel_body }
            </PanelBody>);
}, 10, 2);