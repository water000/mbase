import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import {Link, BrowserRouter as Router, Route } from 'react-router-dom';
import './App.css';
import { Layout, Menu, Breadcrumb, Icon, Badge, Avatar, Select, Dropdown , Row, Col} from 'antd';
import createBrowserHistory from 'history/createBrowserHistory';

const { Header, Content, Footer, Sider } = Layout;
const SubMenu = Menu.SubMenu;
const Option = Select.Option;
const history = createBrowserHistory();

class App extends React.Component{

  constructor(props){
    super(props);
    
    this.state = {
      collapsed : false, 
      selectedMenuIdx:[0]
    }

    if('' === history.location.pathname || '/' == history.location.pathname){
      if(this.props.menu[0].items){
        history.push({pathname:this.props.menu[0].items[0].link});
        this.state.selectedMenuIdx.push(0);
      }else{
        history.push({pathname:this.props.menu[0].link});
      }
    }else{
      this.props.menu.map(
        (m, i) => {
          if(m.items){
            m.items.map((item, j) =>{
              if(item.link == history.location.pathname){
                this.state.selectedMenuIdx = [i, j];
              }
            });
          }else if(m.link == history.location.pathname){
            this.state.selectedMenuIdx = [i];
          }
        }
      );
    }
  }

  componentDidMount(){ 
  }

  onCollapse = (collapsed) => {
    this.setState({collapsed});
  }
 
  getBreadcrumb = (menuindex) => {
    let arr = [];
    arr.push(this.props.menu[menuindex[0]].title);
    if(menuindex.length > 1)
      arr.push(this.props.menu[menuindex[0]].items[menuindex[1]].title);
    return arr;
  }

  onItemClick = ({item, key, keyPath}) => {
    let idx = keyPath[0].split('-'), arr = [parseInt(idx[0])];
    if(idx.length>1)
      arr.push(parseInt(idx[1]));
    this.setState({selectedMenuIdx:arr});
  }

  render() {    
    const idx=this.state.selectedMenuIdx, 
      defaultSelectedKeys = [idx[0]+(idx.length>1?'-'+idx[1]:'')],
      defaultOpenKeys = [idx[0]+''];

    const menu = this.props.menu.map(
      (m, i) => {
        if(m.items)
          return <SubMenu key={i} title={<span><Icon type="user" /><span>{m.title}</span></span>}>
            {m.items.map(
              (item, j) =>
                <Menu.Item key={`${i}-${j}`}><Link to={item.link}>{item.title}</Link></Menu.Item>
            )}
          </SubMenu>
        else if(m.link)
          return <Menu.Item key={i}><Link to={m.link}>{m.title}</Link></Menu.Item>
      }
    );

    const router = this.props.router.map(
      (r, j) =>
        <Route key={j} path={r.path} component={r.component} />
    );
    const DropdownList = (
      <Menu>
        <Menu.Item>
          <a target="_blank" rel="noopener noreferrer" href="http://www.alipay.com/">Profile</a>
        </Menu.Item>
        <Menu.Item>
          <a target="_blank" rel="noopener noreferrer" href="http://www.taobao.com/">Settings</a>
        </Menu.Item>
        <Menu.Item>
          <a target="_blank" rel="noopener noreferrer" href="http://www.tmall.com/">logout</a>
        </Menu.Item>
      </Menu>
    );
    return (
      <Router history={history}>
      <Layout >
        <Sider
          style={{height:'100%', position: 'fixed'}}
          collapsible
          collapsed={this.state.collapsed}
          onCollapse={this.onCollapse}
          breakpoint='xs'
        >
          <div className="App-logo" >YMP</div>
          <Menu 
            theme="dark" 
            defaultSelectedKeys={defaultSelectedKeys} 
            defaultOpenKeys={defaultOpenKeys}
            mode="inline" 
            onClick={this.onItemClick}
            >
            {menu}
          </Menu>
        </Sider>
        <Layout style={{paddingLeft:'200px'}}>
          <Header style={{ background: '#fff' }} >
            <Row gutter={8}>
              <Col span={12}>
                <Breadcrumb>
                  {this.state.selectedMenuIdx && this.getBreadcrumb(this.state.selectedMenuIdx).map((b, i)=>
                    <Breadcrumb.Item>{b}</Breadcrumb.Item>
                  )}
                </Breadcrumb>
              </Col>
              <Col span={12}>
                <ul className='header-ul'>
                  <li><Badge dot><Icon type="bell" style={{ fontSize: 20}} /></Badge></li>
                  <li>
                    <Avatar src="https://zos.alipayobjects.com/rmsportal/ODTLcjxAfvqbxHnVXCYX.png" style={{verticalAlign:"middle", marginRight:'2px'}} />
                    <Dropdown overlay={DropdownList}>
                      <a className="ant-dropdown-link" href="#">
                        tiger <Icon type="down" />
                      </a>
                    </Dropdown>
                  </li>
                </ul>
              </Col>
            </Row>
                      </Header>
          <Content style={{ margin: '20px 50px' }}>
            <div style={{minHeight:'600px' }}>
            <div id='IDD_CONTENT'>
              {router}
            </div>
            </div>
          </Content>
          <Footer style={{ textAlign: 'center' }}>
            ©2016 Created
          </Footer>
        </Layout>
      </Layout>
      </Router>
    );
  }

}

export default App;
