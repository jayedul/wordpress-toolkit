import React, { useState, useRef } from "react";
import { createRoot } from "react-dom/client";

import { MountPoint } from "solidie-materials/mountpoint.jsx";
import { WpDashboardFullPage } from "solidie-materials/backend-dashboard-container/full-page-container.jsx";
import { __, getElementDataSet, isEmpty, data_pointer } from "solidie-materials/helpers.jsx";
import { TextField } from 'solidie-materials/text-field/text-field.jsx';
import { request } from 'solidie-materials/request.jsx';
import { LoadingIcon } from 'solidie-materials/loading-icon/loading-icon.jsx';

const {readonly_mode} = window[data_pointer];

function ActiveScreen(props) {

	const {
		onReconnect,
		license:{
			licensee, 
			plan_name, 
			expires_on, 
			license_key
		}
	} = props;

	const hint_class = 'd-block font-size-15 font-weight-400 line-height-24 letter-spacing--15 color-text-50'.classNames();
	const info_class = 'd-block font-size-15 font-weight-500 line-height-24 letter-spacing--15 color-text'.classNames();
	
	return <div 
		style={{width: '486px', maxWidth: '100%', padding: '35px'}} 
		className={'bg-color-white border-radius-10 margin-auto'.classNames()}
	>
		<div className={'text-align-center'.classNames()}>

			<strong className={'d-block color-text font-size-28 font-weight-600'.classNames()} style={{marginBottom: '20px'}}>
				{__('Congratulations')}
			</strong>

			<span className={'d-block margin-bottom-30 font-size-15 font-weight-400 line-height-20 color-text-50'.classNames()}>
				{__('The license is activated')}
			</span>
		</div>
		
		<div className={'d-flex justify-content-space-between margin-bottom-30'.classNames()}>
			<div>
				<span className={hint_class}>{__('Licensed to')}</span>
				<strong className={info_class}>{licensee}</strong>
			</div>
			<div>
				<span className={hint_class}>{__('Licensed Type')}</span>
				<strong className={info_class}>{plan_name}</strong>
			</div>
			<div>
				<span className={hint_class}>{__('Expires on')}</span>
				<strong className={info_class}>{expires_on || __('Never')}</strong>
			</div>
		</div>

		<div className={'padding-vertical-30 border-radius-5 text-align-center margin-bottom-30'.classNames()} style={{backgroundColor: '#F9F9F9'}}>
			<strong className={'d-block color-text font-size-20 font-weight-600'.classNames()}>
				{license_key}
			</strong>
		</div>

		<button 
			className={'button button-primary button-full-width'.classNames()} 
			onClick={onReconnect}
			disabled={readonly_mode}
		>
			{__('Reconnect')}
		</button>
	</div>
}

function LicenseForm({license={}, configs={}}) {
	
	const ref_wrapper = useRef();
	const [state, setState] = useState({
		key: license?.license_key,
		saving: false,
		error_message: null,
		license: {...license}
	});

	const setVal=(name, value)=>{
		setState({
			...state,
			[name]: value
		});
	}

	const activateKey=()=>{

		setVal('saving', true);
		request('licenseKeySubmit', {license_key: state.key}, resp=>{
			const {success, data:{license, message=__('Something went wrong!')}} = resp;
			setState({
				...state,
				saving: false,
				license,
				error_message: success ? null : message
			});
		});
	}

	return <div style={{paddingTop: '35px', height: '100%'}} className={'padding-horizontal-15'.classNames()}>
		<div className={'margin-auto text-align-center padding-vertical-40'.classNames()}>
			<img src={configs.logo_url} style={{width: '80px', height: 'auto'}}/>
		</div>
		{
			state.license?.activated ? <ActiveScreen license={state.license} onReconnect={()=>setVal('license', null)}/> : 
			<div 
				style={{width: '586px', maxWidth: '100%', padding: '35px'}} 
				className={'bg-color-white border-radius-10 margin-auto d-flex align-items-center column-gap-20'.classNames()}
			>
				<div className={'flex-1'.classNames()}>
					<strong className={'d-block font-size-28 font-weight-600 color-text line-height-30 margin-bottom-10'.classNames()}>
						{configs.screen_label}
					</strong>

					<span className={'d-block font-size-15 font-weight-400 line-height-25 color-text-70 margin-bottom-30'.classNames()}>
						If you have a {configs.app_label} license, The license key should be available on <a href={configs.license_find_url} target="_blank" className={'color-material-70 interactive font-weight-600 hover-underline'.classNames()}>here</a>.
					</span>
					
					<div className={'margin-bottom-30'.classNames()} ref={ref_wrapper}>
						<TextField 
							placeholder={__('Enter your license key here')}
							icon_position="right"
							value={state.key}
							onChange={v=>setVal('key', v)}
							content={
								<button 
									onClick={activateKey} 
									className={'button button-primary button-small margin-left-5'.classNames()}
									disabled={readonly_mode || state.saving || isEmpty(state.key)}
									style={{marginRight: '-10px'}}
								>
									{__('Activate')} <LoadingIcon show={state.saving}/>
								</button>
							}
						/>

						{
							(state.saving || !state.error_message) ? null : 
							<i className={'d-block color-error margin-top-5'.classNames()}>
								{state.error_message}
							</i>
						}

						{
							(state.saving || state.error_message) ? null : 
							<i className={'d-block color-error margin-top-5'.classNames()}>
								{state.license?.message}
							</i>
						}
					</div>
					
					<span className={'d-block font-size-15 font-weight-400 line-height-25 color-text-70'.classNames()}>
						{__('Having trouble?')} <a href={configs.contact_url} target="_blank" className={'color-material-70 interactive font-weight-600 hover-underline'.classNames()}>{__('Contact us')}</a>.
					</span>
				</div>
			</div>
		}
	</div>
}

const license = document.getElementById('solidie_license_page');
if ( license ) {
	createRoot(license).render(
		<MountPoint>
			<WpDashboardFullPage>
				<LicenseForm {...getElementDataSet(license)}/>
				<br/>
				<br/>
			</WpDashboardFullPage>
		</MountPoint>
	)
}
