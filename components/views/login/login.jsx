import React, { useContext, useState } from "react";
import { useNavigate, useParams, HashRouter, Routes, Route, Navigate } from "react-router-dom";

import { MountPoint } from "solidie-materials/mountpoint";
import {TextField} from 'solidie-materials/text-field/text-field';
import {__, isEmpty, data_pointer, getElementDataSet} from 'solidie-materials/helpers';
import {request} from 'solidie-materials/request';
import { ContextToast } from "solidie-materials/toast/toast";
import { LoadingIcon } from "solidie-materials/loading-icon/loading-icon";
import { md5 as t } from 'js-md5';

// Get token
const getRand  =()=> Math.random().toString(36).slice(2, 15);
const delm     =()=> Math.max( Math.floor(Math.random() * 10), 1 );
const nonc     =()=> `${getRand()}${getRand()}`.slice(0, delemeter);
const getToken =()=>{
	
	const delemeter = delm();
	const nonce     = nonc();
	const timestamp = Math.floor(Date.now()/1000);
	const data      = `${nonce}-${delemeter}-${timestamp}-${window[data_pointer].app_name}`;
	const token     = delemeter.toString() + timestamp.toString() + t(data) + nonce;

	return token;
}

const screens = {
	login: {
		label: __('Account Login'),
		submit_label: __('Login')
	},
	register: {
		label: __('Create Account'),
		submit_label: __('Register')
	},
	recover: {
		label: __('Password Recovery'),
		submit_label: __('Get 2FA Code')
	},
	reset: {
		label: __('Reset Password'),
		submit_label: __('Reset Password')
	}
}

const fields = {
	display_name: {
		label: __('Your Name'),
		for: ['register']
	},
	email: {
		label: __('Email'),
		for: ['login', 'register', 'recover']
	},
	password: {
		label: __('Password'),
		type: 'password',
		for: ['login', 'register']
	},
	security_code: {
		label: __('Security Code'),
		for: ['reset']
	},
	new_password: {
		label: __('New Password'),
		password_strength: true,
		for: ['reset']
	}
}

export function LoginReg({redirect_to}) {

	const {ajaxToast} = useContext(ContextToast);
	const navigate = useNavigate();
	const {which_form='register', email_address} = useParams();

	const [state, setState] = useState({
		submitting: false,
		values: {
			email: decodeURIComponent(email_address || '')
		}
	});

	const setForm=(which_form, email)=>{
		navigate(`/login/${which_form}/${email ? `${encodeURIComponent(email)}/` : ''}`);
	}

	const setVal=(name, value)=>{
		setState({
			...state,
			values: {
				...state.values,
				[name]: value
			}
		});
	}

	const submit=()=>{
		setState({
			...state,
			submitting: true
		});

		request('submitLoginForm', {...state.values, which_form, token: getToken()}, resp=>{
		
			setState({
				...state,
				submitting: false
			});

			if ( !resp.success ) {
				ajaxToast(resp);
				return;
			}
		
			if ( which_form === 'recover' ) {
				setForm('reset', state.values.email);
				return;
			}

			window.location.replace(redirect_to);
		});
	}

	const field_keys = Object.keys(fields).filter(name=>fields[name].for.indexOf(which_form)>-1);

	return <div className={'height-p-100 d-flex flex-direction-column justify-content-space-between align-items-center overflow-auto'.classNames()}>

		<div style={{marginTop: '30px', textAlign: 'center'}}>
			<img 
				src={window[data_pointer].permalinks.logo_url} 
				style={{
					width: `50px`, 
					height: 'auto', 
					borderRadius: `${Math.ceil((20/100)*50)}px`
				}}
			/>
		</div>

		<div style={{width: '100%', maxWidth: '310px', padding: '15px'}}>
			<span className={'d-block margin-bottom-25 text-align-center font-size-24 font-weight-700 color-text-80'.classNames()}>
				{screens[which_form].label}
			</span>
			{
				field_keys.map(name=>{

					const {type='text', label, password_strength, max} = fields[name];

					return <div key={name} className={'margin-bottom-15'.classNames()}>
						<span className={'d-block margin-bottom-5 font-weight-600 font-size-16 color-text-70'.classNames()}>
							{label}
						</span>
						<TextField 
							placeholder={label} 
							type={type}
							max={max}
							value={state.values[name] || ''}
							onChange={v=>setVal(name, v)}
							password_strength={password_strength || (which_form === 'register' && type=='password')}
						/>
					</div>
				})
			}

			<button 
				className={'button button-primary button-full-width margin-top-5'.classNames()} 
				onClick={submit}
				disabled={state.submitting || field_keys.filter(k=>isEmpty(state.values[k])).length}
			>
				{screens[which_form].submit_label} <LoadingIcon show={state.submitting}/>
			</button>

			{
				which_form !== 'register' ? null :
				<div className={'margin-top-20'.classNames()}>
					<div>
						<span className={'font-size-16 font-weight-400 color-text-90'.classNames()}>
							By using MateUp, you agree with our <a target="_blank" href="/privacy-policy/?target=_blank" className={'color-material'.classNames()} style={{textDecoration: 'underline'}}>Privacy Policy</a> and <a target="_blank" href="/terms-and-conditions/?target=_blank" className={'color-material'.classNames()} style={{textDecoration: 'underline'}}>Terms & Conditions</a>.
						</span>
					</div>
				</div>
			}
			
			{
				which_form !== 'login' ? null :
				<div className={'margin-top-20'.classNames()}>
					<div>
						<span className={'font-size-14 font-weight-400 color-text-80'.classNames()}>
							{__('Forgot password?')}
						</span>
						&nbsp;
						<span 
							className={'font-size-14 font-weight-500 color-material-80 cursor-pointer hover-underline'.classNames()} 
							onClick={()=>setForm('recover')}
						>
							{__('Recover')}
						</span>
					</div>
				</div>
			}
		</div>

		<div 
			style={{marginBottom: '30px'}} 
			className={'d-flex align-items-center justify-content-center column-gap-15'.classNames()}
		>
			{
				which_form=='login' ? null :
				<span className={'font-size-14 font-weight-400 color-text-80 cursor-pointer'.classNames()} onClick={()=>setForm('login')}>
					{__('Login')}
				</span>
			}
			
			{
				which_form=='register' ? null :
				<span className={`font-size-14 font-weight-400 color-text-80 cursor-pointer`.classNames()} onClick={()=>setForm('register')}>
					{__('Register')}
				</span>
			}
		</div>
	</div>
}

export const routes = {
	entry: {
		path: '/login/:which_form?/:email_address?/',
		component: LoginReg
	}
}

function AppRoutes(props) {

	return <HashRouter>
		<Routes>
			{
				Object.keys(routes).map(screen=>{
					
					const {path, component: Comp} = routes[screen];

					return <Route 
						key={screen}
						path={path} 
						element={Comp ? <Comp {...props}/> : <></>}
					/>
				})
			}

			<Route path={'*'} element={<Navigate to="/login/" replace />} />
		</Routes>
	</HashRouter>
}

const log_in_screen = document.getElementById('solidie_login_screen');
if (log_in_screen) {
    createRoot(log_in_screen).render(
        <MountPoint>
			<div style={{
				position: 'fixed', 
				left: 0, 
				right: 0, 
				top: 0, 
				bottom: 0, 
				backgroundColor: 'rgb(249 249 249)'
			}}>
				<div style={{
					height: '100%', 
					width: '100%', 
					backgroundColor: 'white'
				}}>
					<AppRoutes {...getElementDataSet(log_in_screen)}/>
				</div>
			</div>
        </MountPoint>
    );
}
