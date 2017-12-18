import { Modal, Form, Icon, Input, Button, Checkbox, InputNumber  } from 'antd';
import React from 'react';
import RestFetch from '../RestFetch';

const FormItem = Form.Item;

class NormalLoginForm extends React.Component {
  handleSubmit = (e) => {
    e.preventDefault();
    this.props.form.validateFields((err, values) => {
      if (!err) {
        var param = "";
        for(var k in values)
          param += k + "=" + encodeURIComponent(values[k]) + "&";
        new RestFetch({path:"/user/account/login?"+param}).select()
          .then(rsp=>{
            this.props.onAuthOk();
          })
          .catch(err=>{
            console.log("login err: ", err);
          });
      }
    });
  }
  render() {
    const { getFieldDecorator } = this.props.form;
    return (
      <Form onSubmit={this.handleSubmit} className="login-form">
        <FormItem>
          {getFieldDecorator('phone', {
            rules: [{ required: true, message: 'Please input your phone!' }],
          })(
            <Input prefix={<Icon type="user" style={{ color: 'rgba(0,0,0,.25)' }} />} placeholder="Phone" />
          )}
        </FormItem>
        <FormItem>
          {getFieldDecorator('pwd', {
            rules: [{ required: true, message: 'Please input your Password!' }],
          })(
            <Input prefix={<Icon type="lock" style={{ color: 'rgba(0,0,0,.25)' }} />} type="password" placeholder="Password" />
          )}
        </FormItem>
        <FormItem>
          {getFieldDecorator('remember', {
            valuePropName: 'checked',
            initialValue: true,
          })(
            <Checkbox>Remember me</Checkbox>
          )}
          <Button type="primary" htmlType="submit" style={{width: '100%'}} className="login-form-button">
            Log in
          </Button>
        </FormItem>
      </Form>
    );
  }
}

const WrappedNormalLoginForm = Form.create()(NormalLoginForm);


export default class Auth extends React.Component {
  constructor(props){
    super(props);
  }
  render() {
    return (
      <Modal 
        title="Login"
        width="400"
        visible={this.props.visible}
        footer={null} >
        <WrappedNormalLoginForm onAuthOk={this.props.onAuthOk} />
      </Modal>
    );
  }
}
