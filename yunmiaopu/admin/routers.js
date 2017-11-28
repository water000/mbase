import { BrowserRouter as Router, Route } from 'react-router-dom';
import React, { Component } from 'react';
import App from './App'
import About from './components/About'

const _router_list = [
	{
		path : '/',
		component : App
	},
	{
		path : '/about',
		component : About
	}
];

export default class AppRouters extends React.Component{
	render(){
		return (
			<Router>
			<div className='router'>
			{
				_router_list.map( item => 
					<Route path={item.path} component={item.component} />
				)
			}
			</div>
			</Router>
		)
	}
}