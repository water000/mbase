import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import App from './App'

import About from './components/About'
import Index from './components/Index'
import Permisson from './components/Permission'
import registerServiceWorker from './registerServiceWorker';

const _menu = [
	{title:'Home', items:[{title:'index', link:'/index'}, {title:'about', link:'/about'}]},
	{title:'Permission', link:'/permission'}
]

const _router_list = [
  {
    path : '/index',
    component : Index
  },
  {
    path : '/about',
    component : About
  },
  {
    path : '/permission',
    component : Permisson
  }
];


ReactDOM.render(
	<App menu={_menu} router={_router_list} />, 
	document.getElementById('root'));
registerServiceWorker();
