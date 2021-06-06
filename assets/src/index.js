
import { render, unmountComponentAtNode, Fragment, Component } from '@wordpress/element';
import { ButtonGroup, Button, Dashicon, Modal, Panel, PanelRow, PanelBody, Spinner } from '@wordpress/components';

const { applyFilters } = wp.hooks;
import apiFetch from '@wordpress/api-fetch';
import { __, _n } from '@wordpress/i18n';

import moment from 'moment';
import Interweave from 'interweave';


class EmailModalView extends Component {

    constructor( props ) {
        super( props );
        this.state = {
            isOpen: true,
            isLoading: true,
            total_read_count: 0,
            total_link_click_count: 0,
            to: '',
            subject: '',
            message: '',
            message_plain: '',
            headers: '',
            attachments: '',
            date_time: '',
            read_log: [],
            link_click_log: [],
        };
    }

    componentDidMount() {
        apiFetch( {
            path: '/email-tracker/v1/email/' + this.props.id + '/',
            method: 'GET',
        } ).then( ( res ) => {
            this.setState({
                ...res,
                isLoading: false,
            });
            
            
        }, (error) => {
            this.setState( {
               isLoading: false,
            } );
            alert( 'Error in fetching notes list with the message: ' + error.message + '(' + error.code +')' )
        } );
    }

    setOpen = ( openFlag ) => {
        this.setState({
            isOpen: openFlag
        });
    }

    closeModal = () => {
        this.setOpen( false );
    }

    render() {
        const { id, to, subject } = this.props;

        const title = sprintf( __(' Sub.: %s', 'email-tracker'), subject );
        const icon = 'email-alt';
        const isDismissible = true;
        const focusOnMount = true;
        const shouldCloseOnEsc = true;
        const shouldCloseOnClickOutside = true;

        const iconComponent = icon ? <Dashicon icon={ icon } /> : null;

        const modalProps = {
            icon: iconComponent,
            focusOnMount,
            isDismissible,
            shouldCloseOnEsc,
            shouldCloseOnClickOutside,
            title,
        };

        let modalBody;
        if ( this.state.isLoading ) {
            modalBody = (
                <Spinner color="blue" size="200" />
            );
        } else {
            const moment_local_email = moment.utc( this.state.date_time ).local();

            let read_log_panel_body;
            if ( this.state.read_log.length ) {
                read_log_panel_body = (
                    <PanelRow>
                        <ol>
                            { this.state.read_log.map( read_log => {
                                    let read_local_moment = moment.utc( read_log.date_time ).local();
                                    return (
                                        <li>
                                            { sprintf( __( 'Read at %s on IP %s', 'email-tracker'), read_local_moment.format( 'MMMM Do YYYY, dddd, h:mm:ss a' ) + ' (' + read_local_moment.fromNow() + ')', read_log.ip_address )}
                                        </li>
                                        )
                                }
                            )}
                        </ol>
                    </PanelRow> );
            } else {
                read_log_panel_body = __( 'N/A', 'email-tracker' );
            }
            
            let state = this.state;
            let extra_panel = applyFilters( 'email-tracker-view-email-extra-panel', null, state );
            
            modalBody = (
                <Fragment>
                    <Panel header={ __( 'Email Receiver Activity Log', 'email-tracker') }>
                        <PanelBody title={ "#" + sprintf( _n( '%d time read', '%d times read', parseInt( this.state.total_read_count ), 'email-tracker' ), this.state.total_read_count ) } initialOpen={ this.state.read_log.length ? true : false } >
                            { read_log_panel_body }
                        </PanelBody>
                        { extra_panel }
                    </Panel>
                    <br />
                    <Panel header="Email Data">
                        <PanelBody title={ "To" } initialOpen={ true }>
                            <PanelRow>{ this.state.to }</PanelRow>
                        </PanelBody>
                        <PanelBody title={ "Send Date Time" } initialOpen={ true }>
                            <PanelRow>{ moment_local_email.format( 'MMMM Do YYYY, dddd, h:mm:ss a' ) } ({ moment_local_email.fromNow() })</PanelRow>
                        </PanelBody>
                        { this.state.headers && 
                                    <PanelBody title={ "Headers" } initialOpen={ true }>
                                        <PanelRow>{ ( this.state.headers ) }</PanelRow>
                                    </PanelBody>
                        }
                        { this.state.attachments && 
                                <PanelBody title={ "Attachments" } initialOpen={ true }>
                                    <PanelRow>
                                        { this.state.attachments.split(",\\n").map( attachment => {
                                            let attachment_url = email_tracker.content_url + attachment;
                                            let attachment_split = attachment.split("/");
                                            return (
                                                    <Fragment>
                                                        <a href={attachment_url} target="_blank">{attachment_split[attachment_split.length - 1]}</a>
                                                    </Fragment>
                                                );
                                        })}
                                    </PanelRow>
                                </PanelBody>
                        }
                        <PanelBody title={ "Message" } initialOpen={ true }>
                            <PanelRow>
                                <Interweave content={  this.state.message } />
                            </PanelRow>
                        </PanelBody>
                    </Panel>
                    <br />
                    <Button isDestructive isSmall={ true } onClick={ this.closeModal } >
                        { __('Close', 'email-tracker' ) }
                    </Button>
                </Fragment>
            );
        }

        return (
            <Fragment>
                
                { this.state.isOpen && 
                <Modal { ...modalProps } style={{ minWidth: '75%' }} onRequestClose={ this.closeModal }>
                    { modalBody }
                </Modal>
                }
            </Fragment>
        );
    }
}


window.EMTRLoadView = function EMTRLoadView( id, subject = '' , to = '' ) {
    let passProps = {
        id,
        subject,
        to,
    };

    const root = document.getElementById( 'emtr-email-view-modal-container' );

    unmountComponentAtNode( root );
    render( <EmailModalView {...passProps} />, root );

    return false;
}

